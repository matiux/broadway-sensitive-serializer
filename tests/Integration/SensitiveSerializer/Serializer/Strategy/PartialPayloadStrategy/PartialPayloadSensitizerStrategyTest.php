<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\SensitiveSerializer\MyEvent;
use Tests\Support\SensitiveSerializer\MyEventBuilder;
use Tests\Util\SensitiveSerializer\Key;

class PartialPayloadSensitizerStrategyTest extends TestCase
{
    private UuidInterface $aggregateId;

    private array $ingoingPayload;
    private InMemoryAggregateKeys $aggregateKeys;
    private AES256SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingPayload = [
            'class' => MyEvent::class,
            'payload' => MyEventBuilder::create((string) $this->aggregateId)->build()->serialize(),
        ];

        $this->aggregateKeys = new InMemoryAggregateKeys();
        $this->sensitiveDataManager = new AES256SensitiveDataManager();

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
        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy(new PartialPayloadSensitizerRegistry([]));

        $outgoingPayload = $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        self::assertSame($outgoingPayload, $this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_original_array_if_aggregate_key_does_not_exist(): void
    {
        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        /**
         * Remove aggregate key.
         */
        $aggregateKey = $this->aggregateKeyManager->obtainAggregateKeyOrFail($this->aggregateId);
        $aggregateKey->delete();
        $this->aggregateKeys->update($aggregateKey);

        /**
         * Finally we desensitize data but since there is no aggregate key,
         * they will be the same as the sensitized data.
         */
        $desensitizedOutgoingPayload = $partialPayloadSensitizerStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertEquals($desensitizedOutgoingPayload, $sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_sensitized_array_if_specific_sensitizer_exists(): void
    {
        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        $payload = $sensitizedOutgoingPayload['payload'];

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
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->aggregateId));

        $registry = new PartialPayloadSensitizerRegistry([
            new MyEventSensitizer($this->sensitiveDataManager, $this->aggregateKeyManager, false),
        ]);

        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy($registry);
        $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_does_not_have_the_key_during_encryption(): void
    {
        self::expectException(AggregateKeyEmptyException::class);
        self::expectExceptionMessage(sprintf('Aggregate key is empty but it is required to encrypt data for aggregate %s', (string) $this->aggregateId));

        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($this->aggregateId);
        $aggregateKey->delete();
        $this->aggregateKeys->update($aggregateKey);

        $registry = new PartialPayloadSensitizerRegistry([
            new MyEventSensitizer($this->sensitiveDataManager, $this->aggregateKeyManager, false),
        ]);

        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy($registry);
        $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array_if_specific_sensitizer_exists(): void
    {
        $partialPayloadSensitizerStrategy = new PartialPayloadSensitizerStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        /**
         * Then let's sensitize message.
         */
        $desensitizedOutgoingPayload = $partialPayloadSensitizerStrategy->desensitize($sensitizedOutgoingPayload);

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
class MyEventSensitizer extends PayloadSensitizer
{
    /**
     * {@inheritDoc}
     *
     * @throws AssertionFailedException
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
