<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Exception;
use LogicException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use PHPUnit\Framework\TestCase;

class AES256SensitiveDataManagerTest extends TestCase
{
    /**
     * @return list<list<list<string>|string>>
     */
    public function toEncryptValues(): array
    {
        return [
            ['m.galacci@gmail.com'],
            ['85'],
            ['null'],
            ['false'],
            [['"foo"', '1', '12.0', '12.5']],
        ];
    }

    /**
     * @dataProvider toEncryptValues
     *
     * @param list<string>|string $value
     * @test
     */
    public function it_should_crypt_and_decrypt_sensible_data_without_making_explicit_the_iv($value): void
    {
        $sensitiveDataManager = new AES256SensitiveDataManager(Key::AGGREGATE_MASTER_KEY);

        $encryptedValue = $sensitiveDataManager->encrypt($value);

        self::assertNotSame($encryptedValue, $value);

        $decryptedValue = $sensitiveDataManager->decrypt($encryptedValue);

        self::assertSame($value, $decryptedValue);
    }

    /**
     * @dataProvider toEncryptValues
     *
     * @param list<string>|string $value
     * @test
     */
    public function it_should_encrypt_and_decrypt_sensitive_data_without_encoding_iv($value): void
    {
        $sensitiveDataManager = new AES256SensitiveDataManager(Key::AGGREGATE_MASTER_KEY, null, false);

        $encryptedValue = $sensitiveDataManager->encrypt($value);

        self::assertNotSame($encryptedValue, $value);

        $decryptedEmailValue = $sensitiveDataManager->decrypt($encryptedValue);

        self::assertSame($value, $decryptedEmailValue);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_key_does_not_exist(): void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Secret key not found');

        $sensitiveDataManager = new AES256SensitiveDataManager();

        $sensitiveDataManager->encrypt('m.galacci@gmail.com');
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_different_key(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Decrypt error');

        $sensitiveDataManager = new AES256SensitiveDataManager();

        $encryptedData = $sensitiveDataManager->encrypt('m.galacci@gmail.com', 'foo');
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
