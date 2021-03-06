<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\CustomStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomStrategy\CustomPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomStrategy\CustomStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Ramsey\Uuid\Uuid;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;
use Webmozart\Assert\Assert;

class CustomStrategyTest extends StrategyTest
{
    /**
     * @test
     */
    public function it_should_return_original_array_if_specific_sensitizer_does_not_exist(): void
    {
        $customPayloadSensitizerStrategy = new CustomStrategy(new CustomPayloadSensitizerRegistry([]));

        $outgoingPayload = $customPayloadSensitizerStrategy->sensitize($this->getIngoingPayload());

        self::assertSame($outgoingPayload, $this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_return_original_array_if_aggregate_key_does_not_exist(): void
    {
        $customStrategy = new CustomStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $customStrategy->sensitize($this->getIngoingPayload());

        /**
         * Remove aggregate key.
         */
        $aggregateKey = $this->getAggregateKeyManager()->obtainAggregateKeyOrFail(Uuid::fromString((string) $this->getUserId()));
        $aggregateKey->delete();
        $this->getAggregateKeys()->update($aggregateKey);

        /**
         * Finally we desensitize data but since there is no aggregate key,
         * they will be the same as the sensitized data.
         */
        $desensitizedOutgoingPayload = $customStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertEquals($desensitizedOutgoingPayload, $sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_sensitized_array_if_specific_sensitizer_exists(): void
    {
        $customPayloadSensitizerStrategy = new CustomStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}&array{surname: string, email: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $customPayloadSensitizerStrategy->sensitize($this->getIngoingPayload());

        /**
         * Finally we reveal the aggregate key and decrypt the sensitized data.
         */
        $decryptedAggregateKey = $this->getAggregateKeyManager()->revealAggregateKey(Uuid::fromString((string) $this->getUserId()));

        self::assertNotNull($decryptedAggregateKey);

        $sensitizedPayload = $sensitizedOutgoingPayload['payload'];

        self::assertSensitizedValueSame('Galacci', $sensitizedPayload['surname'], $decryptedAggregateKey);
        self::assertSensitizedValueSame('m.galacci@gmail.com', $sensitizedPayload['email'], $decryptedAggregateKey);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->getUserId()));

        $registry = new CustomPayloadSensitizerRegistry([
            new MyEventSensitizer(
                $this->getSensitiveDataManager(),
                $this->getAggregateKeyManager(),
                $this->getValueSerializer(),
                false
            ),
        ]);

        (new CustomStrategy($registry))->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_does_not_have_the_key_during_encryption(): void
    {
        self::expectException(AggregateKeyEmptyException::class);
        self::expectExceptionMessage(sprintf('Aggregate key is empty but it is required to encrypt data for aggregate %s', (string) $this->getUserId()));

        $aggregateKey = $this->getAggregateKeyManager()->createAggregateKey(Uuid::fromString((string) $this->getUserId()));
        $aggregateKey->delete();
        $this->getAggregateKeys()->update($aggregateKey);

        $registry = new CustomPayloadSensitizerRegistry([
            new MyEventSensitizer(
                $this->getSensitiveDataManager(),
                $this->getAggregateKeyManager(),
                $this->getValueSerializer(),
                false
            ),
        ]);

        (new CustomStrategy($registry))->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array_if_specific_sensitizer_exists(): void
    {
        $customPayloadSensitizerStrategy = new CustomStrategy($this->createRegistryWithSensitizer());

        /**
         * First let's sensitize message.
         */
        $sensitizedOutgoingPayload = $customPayloadSensitizerStrategy->sensitize($this->getIngoingPayload());

        /**
         * Then let's sensitize message.
         */
        $desensitizedOutgoingPayload = $customPayloadSensitizerStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }

    private function createRegistryWithSensitizer(): CustomPayloadSensitizerRegistry
    {
        return new CustomPayloadSensitizerRegistry([
            new MyEventSensitizer(
                $this->getSensitiveDataManager(),
                $this->getAggregateKeyManager(),
                $this->getValueSerializer()
            ),
        ]);
    }
}

/**
 * @psalm-type MyEvent = array{id: string, surname: string, email: string}
 */
class MyEventSensitizer extends PayloadSensitizer
{
    protected function generateSensitizedPayload(): array
    {
        $this->validatePayload($this->getPayload());

        $surname = $this->getPayload()['surname'];
        $email = $this->getPayload()['email'];

        Assert::string($surname);
        Assert::string($email);

        $surname = $this->encryptValue($surname);
        $email = $this->encryptValue($email);

        $payload = $this->getPayload();
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
     * @return array
     */
    protected function generateDesensitizedPayload(): array
    {
        $payload = $this->getPayload();
        $this->validatePayload($payload);

        $payload['surname'] = $this->decryptValue($payload['surname']);
        $payload['email'] = $this->decryptValue($payload['email']);

        return $payload;
    }

    /**
     * @psalm-assert MyEvent $payload
     */
    protected function validatePayload(array $payload): void
    {
        Assert::keyExists($payload, 'id', "Key 'id' should be set in payload.");
        Assert::keyExists($payload, 'surname', "Key 'surname' should be set in payload.");
        Assert::keyExists($payload, 'email', "Key 'email' should be set in payload.");
    }
}
