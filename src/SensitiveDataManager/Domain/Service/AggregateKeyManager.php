<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service;

use GestoreUtenti\Utente\Domain\Exception\ChiaveUtenteException;
use Ramsey\Uuid\UuidInterface;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKey;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKeys;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Exception\AggregateKeyException;

class AggregateKeyManager
{
    private KeyGenerator $keyGenerator;
    private AggregateKeys $aggregateKeys;
    private SensitiveDataManager $sensitiveDataManager;
    private string $aggregateMasterKey;

    public function __construct(
        KeyGenerator $keyGenerator,
        AggregateKeys $aggregateKeys,
        SensitiveDataManager $sensitiveDataManager,
        string $aggregateMasterKey
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->aggregateKeys = $aggregateKeys;
        $this->sensitiveDataManager = $sensitiveDataManager;
        $this->aggregateMasterKey = $aggregateMasterKey;
    }

    /**
     * Returns the decrypted aggregate_key.
     * Persists the aggregate_key encrypted with the aggregate_master_key.
     *
     * @param UuidInterface $aggregateId
     *
     * @return AggregateKey
     */
    public function createAggregateKey(UuidInterface $aggregateId): AggregateKey
    {
        // Aggregate key (AGGREGATE_KEY) encrypted with the AGGREGATE_MASTER_KEY
        $encryptedAggregateKey = $this->sensitiveDataManager->encrypt(
            $this->keyGenerator->generate(),
            $this->aggregateMasterKey
        );

        $aggregateKey = AggregateKey::create($aggregateId, $encryptedAggregateKey);
        $this->aggregateKeys->add($aggregateKey);

        return $aggregateKey;
    }

    /**
     * @param UuidInterface $aggregateId
     *
     * @throws AggregateKeyException
     *
     * @return null|string
     */
    public function revealAggregateKey(UuidInterface $aggregateId): ?string
    {
        $aggregateKey = $this->obtainAggregateKeyOrFail($aggregateId);

        if ($aggregateKey->exists()) {
            return $this->sensitiveDataManager->decrypt((string) $aggregateKey, $this->aggregateMasterKey);
        }

        return null;
    }

    /**
     * @param UuidInterface $aggregateId
     *
     * @throws AggregateKeyException
     *
     * @return AggregateKey
     */
    public function obtainAggregateKeyOrFail(UuidInterface $aggregateId): AggregateKey
    {
        if (!$aggregateKey = $this->aggregateKeys->withAggregateId($aggregateId)) {
            throw AggregateKeyException::keyNotFound($aggregateId);
        }

        return $aggregateKey;
    }
}
