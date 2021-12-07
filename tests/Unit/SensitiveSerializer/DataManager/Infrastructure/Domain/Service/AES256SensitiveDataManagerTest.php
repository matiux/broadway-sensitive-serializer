<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Exception;
use LogicException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use PHPUnit\Framework\TestCase;
use Tests\Util\SensitiveSerializer\Key;

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
    public function it_should_crypt_and_decrypt_sensible_data_without_making_explicit_the_iv(): void
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
    public function it_should_encrypt_and_decrypt_sensitive_data_without_encoding_iv(): void
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

    /**
     * @test
     */
    public function it_should_throw_exception_if_iv_is_invalid(): void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Problems in IV recognizing');

        $sensitiveDataManager = new AES256SensitiveDataManager();

        $sensitiveDataManager->decrypt(':foo:foo');
    }
}
