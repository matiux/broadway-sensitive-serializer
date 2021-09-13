<?php

declare(strict_types=1);

namespace Test\Unit\SensitizedEventStore\Dbal\SensitiveDataManager\Infrastructure\Domain\Service;

use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use SensitizedEventStore\Dbal\SensitiveDataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Test\Util\Key;

class AES256SensitiveDataManagerTest extends TestCase
{
    private string $emailAddress;

    protected function setUp(): void
    {
        $this->emailAddress = 'm.galacci@gmail.com';
    }

    /**
     * @test
     */
    public function crypt_e_decrypt_di_un_dato_sensibile_senza_esplicitare_iv(): void
    {
        $sensitiveDataManager = new AES256SensitiveDataManager(Key::AGGREGATE_MASTER_KEY);

        $encryptedEmailAddress = $sensitiveDataManager->encrypt($this->emailAddress);

        self::assertNotSame($encryptedEmailAddress, $this->emailAddress);

        $decryptedEmailAddress = $sensitiveDataManager->decrypt($encryptedEmailAddress);

        self::assertSame($this->emailAddress, $decryptedEmailAddress);
    }

    /**
     * @test
     */
    public function crypt_e_decrypt_di_un_dato_sensibile_senza_encoding_iv(): void
    {
        $sensitiveDataManager = new AES256SensitiveDataManager(Key::AGGREGATE_MASTER_KEY, null, false);

        $encryptedEmailAddress = $sensitiveDataManager->encrypt($this->emailAddress);

        self::assertNotSame($encryptedEmailAddress, $this->emailAddress);

        $decryptedEmailAddress = $sensitiveDataManager->decrypt($encryptedEmailAddress);

        self::assertSame($this->emailAddress, $decryptedEmailAddress);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_key_does_not_exist(): void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Secret key not found');

        $sensitiveDataManager = new AES256SensitiveDataManager();

        $sensitiveDataManager->encrypt($this->emailAddress);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_different_key(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Decrypt error');

        $sensitiveDataManager = new AES256SensitiveDataManager();

        $encryptedData = $sensitiveDataManager->encrypt($this->emailAddress, 'foo');
        $sensitiveDataManager->decrypt($encryptedData, 'bar');
    }
}
