<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Exception;
use LogicException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;

final class AES256SensitiveDataManager implements SensitiveDataManager
{
    private const IV_SEPARATOR = ':';

    public const CIPHER_METHOD = 'aes-256-cbc';
    private ?string $secretKey;
    private string $iv;
    private bool $ivEncoding;

    public function __construct(
        ?string $secretKey = null,
        string $iv = null,
        bool $ivEncoding = true
    ) {
        $this->secretKey = $secretKey;
        $this->iv = $iv ?? self::generateIV();
        $this->ivEncoding = $ivEncoding;
    }

    public function encrypt(string $sensitiveData, string $secretKey = null): string
    {
        $key = $this->getKeyOrFail($secretKey);

        $encrypted = openssl_encrypt($sensitiveData, self::CIPHER_METHOD, $key, 0, $this->iv);

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
     * @param string      $encryptedSensitiveData
     * @param null|string $secretKey
     *
     * @throws Exception
     *
     * @return string
     */
    public function decrypt(string $encryptedSensitiveData, string $secretKey = null): string
    {
        $data = $this->prepareData($encryptedSensitiveData);
        $key = $this->getKeyOrFail($secretKey);

        if (!$decrypted = openssl_decrypt($data['encrypted_data'], self::CIPHER_METHOD, $key, 0, $data['iv'])) {
            $errors = [];

            while ($msg = openssl_error_string()) {
                $errors[] = $msg;
            }

            throw new Exception('Decrypt error: '.implode(' + ', $errors));
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
                // The IV is inside the encrypted data
                return [
                    'encrypted_data' => $this->stripIsSensitizedIndicator($parts[0]),
                    'iv' => base64_decode($parts[1]),
                ];
            default:
                // TODO
                throw new LogicException('Problems in IV recognizing');
        }
    }

    private function stripIsSensitizedIndicator(string $encryptedData): string
    {
        return preg_replace(sprintf('/^%s/', self::IS_SENSITIZED_INDICATOR), '', $encryptedData);
    }

    /**
     * @param null|string $secretKey
     *
     * @throws LogicException
     *
     * @return string
     */
    private function getKeyOrFail(string $secretKey = null): string
    {
        /** @infection-ignore-all */
        if (!$key = $secretKey ?? $this->secretKey) {
            throw new LogicException('Secret key not found');
        }

        return $key;
    }

    public static function generateIV(): string
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
    }

    public function iv(): string
    {
        return $this->iv;
    }
}
