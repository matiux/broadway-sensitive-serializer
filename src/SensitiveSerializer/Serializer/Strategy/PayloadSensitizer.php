<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;
use Ramsey\Uuid\Uuid;

abstract class PayloadSensitizer
{
    private SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;
    private ValueSerializer $valueSerializer;

    /**
     * @var array<array-key, null|array|scalar>
     */
    private array $payload = [];

    /**
     * @var class-string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private string $type;

    private bool $automaticAggregateKeyCreation;

    private string $decryptedAggregateKey = '';

    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        ValueSerializer $valueSerializer,
        bool $automaticAggregateKeyCreation = true
    ) {
        $this->sensitiveDataManager = $sensitiveDataManager;
        $this->aggregateKeyManager = $aggregateKeyManager;
        $this->valueSerializer = $valueSerializer;
        $this->automaticAggregateKeyCreation = $automaticAggregateKeyCreation;
    }

    /**
     * @param array $serializedObject
     *
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|DuplicatedAggregateKeyException
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Assert::isSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $this->type = $serializedObject['class'];

        $aggregateId = $serializedObject['payload']['id'];

        $this->decryptedAggregateKey = $this->automaticAggregateKeyCreation ?
            $this->createAggregateKeyIfDoesNotExist($aggregateId) :
            $this->obtainDecryptedAggregateKeyOrFail($aggregateId);

        $serializedObject['payload'] = $this->generateSensitizedPayload();

        return $serializedObject;
    }

    /**
     * @param string $aggregateId
     *
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|DuplicatedAggregateKeyException
     *
     * @return string
     */
    private function createAggregateKeyIfDoesNotExist(string $aggregateId): string
    {
        try {
            $decryptedAggregateKey = $this->obtainDecryptedAggregateKeyOrFail($aggregateId);
        } catch (AggregateKeyNotFoundException $e) {
            $this->aggregateKeyManager->createAggregateKey(Uuid::fromString($aggregateId));

            $decryptedAggregateKey = $this->obtainDecryptedAggregateKeyOrFail($aggregateId);
        }

        return $decryptedAggregateKey;
    }

    /**
     * @param string $aggregateId
     *
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException
     *
     * @return string
     */
    private function obtainDecryptedAggregateKeyOrFail(string $aggregateId): string
    {
        $id = Uuid::fromString($aggregateId);

        if (!$decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($id)) {
            throw AggregateKeyEmptyException::create($id);
        }

        return $decryptedAggregateKey;
    }

    abstract protected function generateSensitizedPayload(): array;

    public function desensitize(array $serializedObject): array
    {
        Assert::isSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $this->type = $serializedObject['class'];
        $aggregateId = $serializedObject['payload']['id'];

        try {
            $this->decryptedAggregateKey = $this->obtainDecryptedAggregateKeyOrFail($aggregateId);
            $serializedObject['payload'] = $this->generateDesensitizedPayload();

            return $serializedObject;
        } catch (AggregateKeyEmptyException|AggregateKeyNotFoundException $e) {
            return $serializedObject;
        }
    }

    abstract protected function generateDesensitizedPayload(): array;

    /**
     * @template T of \Broadway\Serializer\Serializable
     *
     * @param array|T $subject
     *
     * @return bool
     */
    abstract public function supports($subject): bool;

    /**
     * @return array<array-key, null|array|scalar>
     */
    protected function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return class-string
     */
    protected function getType(): string
    {
        return $this->type;
    }

    /**
     * @param null|array<int, mixed>|scalar $value
     *
     * @return string
     */
    protected function encryptValue($value): string
    {
        Assert::isSerializable($value);

        return $this->sensitiveDataManager->encrypt(
            $this->valueSerializer->serialize($value),
            $this->decryptedAggregateKey
        );
    }

    /**
     * @param string $value
     *
     * @return null|array<int, mixed>|scalar
     */
    protected function decryptValue(string $value)
    {
        $decryptedValue = $this->sensitiveDataManager->decrypt(
            $value,
            $this->decryptedAggregateKey
        );

        return $this->valueSerializer->deserialize($decryptedValue);
    }
}
