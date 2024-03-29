<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;

final class AES256SensitiveDataManager extends SensitiveDataManager
{
    private const IV_SEPARATOR = ':';

    public const CIPHER_METHOD = 'aes-256-cbc';
    private string $iv;
    private bool $ivEncoding;

    public function __construct(
        ?string $secretKey = null,
        ?string $iv = null,
        bool $ivEncoding = true
    ) {
        parent::__construct($secretKey);

        $this->iv = $iv ?? openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $this->ivEncoding = $ivEncoding;
    }

    /**
     * {@inheritDoc}
     */
    protected function doEncrypt(string $sensitiveData): string
    {
        $encrypted = openssl_encrypt($sensitiveData, self::CIPHER_METHOD, $this->secretKey(), 0, $this->iv);

        if (!$this->ivEncoding) {
            return $encrypted;
        }

        return sprintf(
            '%s%s%s%s',
            self::IS_SENSITIZED_INDICATOR,
            $encrypted,
            self::IV_SEPARATOR,
            base64_encode($this->iv)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function doDecrypt(string $encryptedSensitiveData): string
    {
        $data = $this->prepareData($encryptedSensitiveData);

        if (!$decrypted = openssl_decrypt($data['encrypted_data'], self::CIPHER_METHOD, $this->secretKey(), 0, $data['iv'])) {
            $errors = [];

            while ($msg = openssl_error_string()) {
                $errors[] = $msg;
            }

            throw new \Exception('Decrypt error: '.implode(' + ', $errors));
        }

        return $decrypted;
    }

    /**
     * @param string $encryptedSensitiveData
     *
     * @return array{encrypted_data: string, iv: string}
     */
    private function prepareData(string $encryptedSensitiveData): array
    {
        $parts = explode(self::IV_SEPARATOR, $encryptedSensitiveData);

        switch (count($parts)) {
            case 1:
                // The IV is not inside the encrypted data, we use the default one
                return [
                    'encrypted_data' => $this->stripIsSensitizedIndicator($encryptedSensitiveData),
                    'iv' => $this->iv,
                ];
            case 2:
                // The VI is inside the encrypted data
                return [
                    'encrypted_data' => $this->stripIsSensitizedIndicator($parts[0]),
                    'iv' => base64_decode($parts[1]),
                ];
            default:
                // TODO
                throw new \LogicException('Problems in IV recognizing');
        }
    }

    private function stripIsSensitizedIndicator(string $encryptedData): string
    {
        return (string) preg_replace('/^'.preg_quote(self::IS_SENSITIZED_INDICATOR, '/').'/', '', $encryptedData);
    }
}
