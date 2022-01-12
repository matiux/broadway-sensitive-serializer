<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialStrategy;

trait PartialStrategyTestUtil
{
    protected function buildPartialStrategy(PartialPayloadSensitizerRegistry $registry): PartialStrategy
    {
        return new PartialStrategy(
            $registry,
            $this->buildPartialPayloadSensitizer($registry)
        );
    }

    protected function buildPartialPayloadSensitizer(
        PartialPayloadSensitizerRegistry $registry,
        bool $automaticAggregateKeyCreation = true
    ): PartialPayloadSensitizer {
        return new PartialPayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $registry,
            $automaticAggregateKeyCreation
        );
    }
}
