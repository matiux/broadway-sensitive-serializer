<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy;

use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

abstract class PayloadSensitizer
{
    private SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;
    private array $payload = [];
    /**
     * @var class-string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private string $type;
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
     * @param array $serializedObject
     *
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|AssertionFailedException|DuplicatedAggregateKeyException
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $this->type = $serializedObject['class'];

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
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|DuplicatedAggregateKeyException
     *
     * @return string
     */
    private function createAggregateKeyIfDoesNotExist(string $aggregateId): string
    {
        try {
            $decryptedAggregateKey = $this->obtainDecryptedAggregateKeyOrError($aggregateId);
        } catch (AggregateKeyNotFoundException $e) {
            $this->aggregateKeyManager->createAggregateKey(Uuid::fromString($aggregateId));
            $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey(Uuid::fromString($aggregateId));

            Assert::string($decryptedAggregateKey);
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
    private function obtainDecryptedAggregateKeyOrError(string $aggregateId): string
    {
        $id = Uuid::fromString($aggregateId);

        if (!$decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($id)) {
            throw AggregateKeyEmptyException::create($id);
        }

        return $decryptedAggregateKey;
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @return array
     */
    abstract protected function generateSensitizedPayload(string $decryptedAggregateKey): array;

    /**
     * @throws AggregateKeyNotFoundException|AssertionFailedException
     */
    public function desensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        $this->payload = $serializedObject['payload'];
        $this->type = $serializedObject['class'];
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

//    protected function getSensitiveDataManager(): SensitiveDataManager
//    {
//        return $this->sensitiveDataManager;
//    }

    /**
     * @param null|array|scalar $value
     *
     * @return string
     */
    protected function encrypt($value): string
    {
        // Se $value è oggetto = errore
    }

    /**
     * @param string $value
     *
     * @return null|array|scalar
     */
    protected function decrypt(string $value)
    {
        // Se non è serializzabile = errore
    }
}
