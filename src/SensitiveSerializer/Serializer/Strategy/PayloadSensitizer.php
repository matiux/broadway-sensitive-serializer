<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy;

use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

abstract class PayloadSensitizer
{
    protected SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;
    protected array $payload = [];
    private bool $automaticAggregateKeyCreation;

    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        bool $automaticAggregateKeyCreation = true
    ) {
        $this->sensitiveDataManager = $sensitiveDataManager;
        $this->aggregateKeyManager = $aggregateKeyManager;
        $this->automaticAggregateKeyCreation = $automaticAggregateKeyCreation;
    }

    /**
     * @throws AggregateKeyException|AssertionFailedException
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $aggregateId = $serializedObject['payload']['id'];

        $decryptedAggregateKey = $this->automaticAggregateKeyCreation ?
            $this->createAggregateKeyIfDoesNotExist($aggregateId) :
            $this->obtainDecryptedAggregateKeyOrError($aggregateId);

        $serializedObject['payload'] = $this->generateSensitizedPayload($decryptedAggregateKey);

        return $serializedObject;
    }

    /**
     * @param string $aggregateId
     *
     * @throws AggregateKeyException
     *
     * @return string
     */
    private function createAggregateKeyIfDoesNotExist(string $aggregateId): string
    {
        try {
            $decryptedAggregateKey = $this->obtainDecryptedAggregateKeyOrError($aggregateId);
        } catch (AggregateKeyException $e) {
            $this->aggregateKeyManager->createAggregateKey(Uuid::fromString($aggregateId));
            $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey(Uuid::fromString($aggregateId));

            Assert::string($decryptedAggregateKey);
        }

        return $decryptedAggregateKey;
    }

    /**
     * @param string $aggregateId
     *
     * @throws AggregateKeyException
     *
     * @return string
     */
    private function obtainDecryptedAggregateKeyOrError(string $aggregateId): string
    {
        $id = Uuid::fromString($aggregateId);

        if (!$decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($id)) {
            throw AggregateKeyException::keyRequired($id);
        }

        return $decryptedAggregateKey;
    }

    abstract protected function generateSensitizedPayload(string $decryptedAggregateKey): array;

    /**
     * @throws AggregateKeyException|AssertionFailedException
     */
    public function desensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $aggregateId = $serializedObject['payload']['id'];

        if (!$decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey(Uuid::fromString($aggregateId))) {
            return $serializedObject;
        }

        $serializedObject['payload'] = $this->generateDesensitizedPayload($decryptedAggregateKey);

        return $serializedObject;
    }

    abstract protected function generateDesensitizedPayload(string $decryptedAggregateKey): array;

    /**
     * @template T of \Broadway\Serializer\Serializable
     *
     * @param array|T $subject
     *
     * @return bool
     */
    abstract public function supports($subject): bool;
}
