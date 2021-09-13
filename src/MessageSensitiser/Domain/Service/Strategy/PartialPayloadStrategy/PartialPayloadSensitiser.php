<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy;

use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Exception\AggregateKeyException;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\AggregateKeyManager;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\SensitiveDataManager;

/**
 * @template S of mixed
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class PartialPayloadSensitiser
{
    protected SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;

    /**
     * @var S
     */
    protected $payload;

    private DomainMessage $domainMessage;

    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager
    ) {
        $this->sensitiveDataManager = $sensitiveDataManager;
        $this->aggregateKeyManager = $aggregateKeyManager;
    }

    /**
     * @param DomainMessage $domainMessage
     *
     * @throws AggregateKeyException
     *
     * @return DomainMessage
     */
    public function sensitise(DomainMessage $domainMessage): DomainMessage
    {
        /** @var S $this->payload */
        $this->payload = $domainMessage->getPayload();
        $this->domainMessage = $domainMessage;

        if (!$decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey(
            Uuid::fromString($domainMessage->getId())
        )
        ) {
            return $domainMessage;
        }

        return $this->regenerateDomainMessage(
            $this->generateSensitisedPayload($decryptedAggregateKey)
        );
    }

    /**
     * @return S
     */
    abstract protected function generateSensitisedPayload(string $decryptedAggregateKey);

    /**
     * @param S $payload
     *
     * @return DomainMessage
     */
    protected function regenerateDomainMessage($payload): DomainMessage
    {
        return DomainMessage::recordNow(
            $this->domainMessage->getId(),
            $this->domainMessage->getPlayhead(),
            $this->domainMessage->getMetadata(),
            $payload
        );
    }

    /**
     * @param S $subject
     *
     * @return bool
     */
    abstract public function supports($subject): bool;
}
