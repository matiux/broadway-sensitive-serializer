<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Util;

abstract class SensitiveDataManager
{
    public const IS_SENSITIZED_INDICATOR = '#-#';

    private ?string $secretKey;

    public function __construct(
        ?string $secretKey = null
    ) {
        $this->secretKey = $secretKey;
    }

    /**
     * @param list<string>|string $sensitiveData
     * @param null|string         $secretKey
     *
     * @return list<string>|string
     */
    public function encrypt($sensitiveData, ?string $secretKey = null)
    {
        $this->setSecretKey($secretKey);
        $this->validate($sensitiveData);

        if (is_array($sensitiveData)) {
            return $this->encryptArray($sensitiveData);
        }

        return $this->doEncrypt($sensitiveData);
    }

    /**
     * @param list<string> $sensitiveData
     *
     * @return list<string>
     */
    private function encryptArray(array $sensitiveData): array
    {
        $encryptedValues = [];

        foreach ($sensitiveData as $item) {
            $encryptedValues[] = $this->doEncrypt($item);
        }

        return $encryptedValues;
    }

    /**
     * @param string $sensitiveData
     *
     * @return string
     */
    abstract protected function doEncrypt(string $sensitiveData): string;

    /**
     * @param list<string>|string $encryptedSensitiveData
     * @param null|string         $secretKey
     *
     * @return list<string>|string
     */
    public function decrypt($encryptedSensitiveData, ?string $secretKey = null)
    {
        !$secretKey ?: $this->secretKey = $secretKey;

        $this->validate($encryptedSensitiveData);

        if (is_array($encryptedSensitiveData)) {
            return $this->decryptArray($encryptedSensitiveData);
        }

        return $this->doDecrypt($encryptedSensitiveData);
    }

    /**
     * @param list<string> $value
     *
     * @return list<string>
     */
    private function decryptArray(array $value): array
    {
        $decryptedValues = [];

        foreach ($value as $item) {
            $decryptedValues[] = $this->doDecrypt($item);
        }

        return $decryptedValues;
    }

    /**
     * @param string $encryptedSensitiveData
     *
     * @return string
     */
    abstract protected function doDecrypt(string $encryptedSensitiveData): string;

    /**
     * TODO - Code duplication.
     *
     * @param mixed $value
     */
    private function validate($value): void
    {
        if (Util::isAssociativeArray($value)) {
            throw new \InvalidArgumentException('You cannot serialize an associative array');
        }

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (is_object($value)) {
            throw new \InvalidArgumentException('ValueSerializer::serialize() cannot accept objects');
        }
    }

    private function setSecretKey(?string $secretKey): void
    {
        if ($secretKey) {
            $this->secretKey = $secretKey;
        }

        if (!$this->secretKey) {
            throw new \LogicException('Secret key not found');
        }
    }

    protected function secretKey(): string
    {
        if (!$this->secretKey) {
            throw new \LogicException('Secret key not found');
        }

        return $this->secretKey;
    }
}
