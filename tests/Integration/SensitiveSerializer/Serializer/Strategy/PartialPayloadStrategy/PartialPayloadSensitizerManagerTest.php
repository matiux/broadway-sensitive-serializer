<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerRegistry;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\InMemoryAggregateKeys;
use Tests\Support\MyEvent;
use Tests\Support\MyEventBuilder;
use Tests\Util\Key;

class PartialPayloadSensitizerManagerTest extends TestCase
{
    private UuidInterface $aggregateId;

    private array $ingoingPayload;
    private AES256SensitiveDataManager $sensitiveDataManager;
    private InMemoryAggregateKeys $aggregateKeys;
    private AggregateKeyManager $aggregateKeyManager;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingPayload = [
            'class' => MyEvent::class,
            'payload' => MyEventBuilder::create((string) $this->aggregateId)->build()->serialize(),
        ];

        $this->sensitiveDataManager = new AES256SensitiveDataManager();
        $this->aggregateKeys = new InMemoryAggregateKeys();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @test
     */
    public function it_should_return_original_array_if_specific_sensitizer_does_not_exist(): void
    {
        $sensitizerManager = new PartialPayloadSensitizerManager(new PartialPayloadSensitizerRegistry([]));

        $outgoingPayload = $sensitizerManager->sensitize($this->ingoingPayload);

        self::assertSame($outgoingPayload, $this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_original_array_if_aggregate_key_does_not_exist(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizerManager = new PartialPayloadSensitizerManager($this->createRegistryWithSensitizer());
        $sensitizedOutgoingPayload = $sensitizerManager->sensitize($this->ingoingPayload);

        /**
         * Remove aggregate key.
         */
        $aggregateKey->delete();
        $this->aggregateKeys->update($aggregateKey);

        /**
         * Finally we desensitize data but since there is no aggregate key,
         * they will be the same as the sensitized data.
         */
        $desensitizedOutgoingPayload = $sensitizerManager->desensitize($sensitizedOutgoingPayload);

        self::assertEquals($desensitizedOutgoingPayload, $sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_sensitized_array_if_specific_sensitizer_exists(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $sensitizerManager = new PartialPayloadSensitizerManager($this->createRegistryWithSensitizer());

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $sensitizerManager->sensitize($this->ingoingPayload);

        $payload = $sensitizedOutgoingPayload['payload'];
        self::assertArrayHasKey('surname', $payload);

        /**
         * Finally we reveal the aggregate key and decrypt the sensitized data.
         */
        $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($this->aggregateId);

        self::assertSame('Galacci', $this->sensitiveDataManager->decrypt($payload['surname'], $decryptedAggregateKey));
        self::assertSame('m.galacci@gmail.com', $this->sensitiveDataManager->decrypt($payload['email'], $decryptedAggregateKey));
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->aggregateId));

        $sensitizerManager = new PartialPayloadSensitizerManager($this->createRegistryWithSensitizer());
        $sensitizerManager->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_does_not_have_the_key_during_encryption(): void
    {
        self::expectException(AggregateKeyException::class);
        self::expectExceptionMessage(sprintf('Aggregate key is required to encrypt data for aggregate %s', (string) $this->aggregateId));

        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($this->aggregateId);
        $aggregateKey->delete();
        $this->aggregateKeys->update($aggregateKey);

        $sensitizerManager = new PartialPayloadSensitizerManager($this->createRegistryWithSensitizer());
        $sensitizerManager->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array_if_specific_sensitizer_exists(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $sensitizerManager = new PartialPayloadSensitizerManager($this->createRegistryWithSensitizer());

        /**
         * Then let's sensitize message.
         */
        $sensitizedOutgoingPayload = $sensitizerManager->sensitize($this->ingoingPayload);

        $desensitizedOutgoingPayload = $sensitizerManager->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->ingoingPayload, $desensitizedOutgoingPayload);
    }

    private function createRegistryWithSensitizer(): PartialPayloadSensitizerRegistry
    {
        return new PartialPayloadSensitizerRegistry([
            new MyEventSensitizer($this->sensitiveDataManager, $this->aggregateKeyManager),
        ]);
    }
}

/**
 * @psalm-type MyEvent = array{id: string, surname: string, email: string}
 */
class MyEventSensitizer extends PartialPayloadSensitizer
{
    /**
     * @param string $decryptedAggregateKey
     *
     * @throws AssertionFailedException
     *
     * @return array
     */
    protected function generateSensitizedPayload(string $decryptedAggregateKey): array
    {
        $this->validatePayload($this->payload);

        $surname = $this->sensitiveDataManager->encrypt($this->payload['surname'], $decryptedAggregateKey);
        $email = $this->sensitiveDataManager->encrypt($this->payload['email'], $decryptedAggregateKey);

        $payload = $this->payload;
        $payload['surname'] = $surname;
        $payload['email'] = $email;

        return $payload;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($subject): bool
    {
        return true;
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @throws AssertionFailedException
     *
     * @return array
     */
    protected function generateDesensitizedPayload(string $decryptedAggregateKey): array
    {
        $this->validatePayload($this->payload);

        $surname = $this->sensitiveDataManager->decrypt($this->payload['surname'], $decryptedAggregateKey);
        $email = $this->sensitiveDataManager->decrypt($this->payload['email'], $decryptedAggregateKey);

        $payload = $this->payload;
        $payload['surname'] = $surname;
        $payload['email'] = $email;

        return $payload;
    }

    /**
     * @psalm-assert MyEvent $payload
     *
     * @throws AssertionFailedException
     */
    protected function validatePayload(array $payload): void
    {
        Assert::keyExists($payload, 'id', "Key 'id' should be set in payload.");
        Assert::keyExists($payload, 'surname', "Key 'surname' should be set in payload.");
        Assert::keyExists($payload, 'email', "Key 'email' should be set in payload.");
    }
}
