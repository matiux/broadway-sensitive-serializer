<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Adbar\Dot;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;

final class PartialPayloadSensitizer extends PayloadSensitizer
{
    private PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry;

    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        ValueSerializer $valueSerializer,
        PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry,
        bool $automaticAggregateKeyCreation = true
    ) {
        parent::__construct($sensitiveDataManager, $aggregateKeyManager, $valueSerializer, $automaticAggregateKeyCreation);

        $this->partialPayloadSensitizerRegistry = $partialPayloadSensitizerRegistry;
    }

    /**
     * @throws \Exception|\LogicException
     */
    protected function generateSensitizedPayload(): array
    {
        $sensitizedKeys = new Dot([]);
        $payload = new Dot($this->getPayload());

        foreach ($this->obtainToSensitizeKeysOrFail() as $toSensitizeKey) {
            if ($payload->has($toSensitizeKey)) {
                /** @var null|array<int, mixed>|scalar $value */
                $value = $payload->get($toSensitizeKey);
                $sensitizedKeys->set($toSensitizeKey, $this->encryptValue($value));
            }
        }

        $sensitizedPayload = $payload->mergeRecursiveDistinct($sensitizedKeys)->jsonSerialize();
        ksort($sensitizedPayload);

        return $sensitizedPayload;
    }

    /**
     * @throws \Exception|\LogicException
     *
     * @return string[]
     */
    private function obtainToSensitizeKeysOrFail(): array
    {
        if (!$toSensitizeKeys = $this->partialPayloadSensitizerRegistry->resolveItemFor($this->getType())) {
            throw new \LogicException(
                sprintf('If you are here, the strategy should have identified correct event in registry: %s', $this->getType())
            );
        }

        return $toSensitizeKeys;
    }

    /**
     * @throws \Exception|\LogicException
     *
     * @return array
     */
    protected function generateDesensitizedPayload(): array
    {
        $desensitizedKeys = new Dot([]);
        $payload = new Dot($this->getPayload());

        foreach ($this->obtainToSensitizeKeysOrFail() as $toSensitizeKey) {
            if ($payload->has($toSensitizeKey)) {
                /** @var list<string>|string $value */
                $value = $payload->get($toSensitizeKey);
                $desensitizedKeys->set($toSensitizeKey, $this->decryptValue($value));
            }
        }

        $desensitizedPayload = $payload->mergeRecursiveDistinct($desensitizedKeys)->jsonSerialize();
        ksort($desensitizedPayload);

        return $desensitizedPayload;
    }

    public function supports($subject): bool
    {
        throw new \BadMethodCallException();
    }
}
