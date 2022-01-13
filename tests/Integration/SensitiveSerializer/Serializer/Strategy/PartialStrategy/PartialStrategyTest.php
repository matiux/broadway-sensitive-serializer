<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;
use Tests\Support\SensitiveSerializer\MyEvent;

class PartialStrategyTest extends StrategyTest
{
    use PartialStrategyTestUtil;

    /**
     * @test
     */
    public function it_should_return_original_payload_if_registry_does_not_support_event_type(): void
    {
        $partialStrategy = $this->buildPartialStrategy(new PartialPayloadSensitizerRegistry([]));

        $sensitizedOutgoingPayload = $partialStrategy->sensitize($this->getIngoingPayload());

        self::assertSame($this->getIngoingPayload(), $sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_sensitize_payload_if_registry_supports_event_type(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->getAggregateKeyManager()->createAggregateKey($this->getAggregateId());

        $partialStrategy = $this->buildPartialStrategy(new PartialPayloadSensitizerRegistry([MyEvent::class => ['surname', 'email']]));

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);
        $this->assertSensitizedEqualToExpected($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);
    }

    /**
     * @test
     */
    public function it_should_desensitize_payload_if_registry_supports_event_type(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->getAggregateKeyManager()->createAggregateKey($this->getAggregateId());

        $partialStrategy = $this->buildPartialStrategy(new PartialPayloadSensitizerRegistry([MyEvent::class => ['surname', 'email']]));

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);

        $desensitizedOutgoingPayload = $partialStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }
}