<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholePayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholePayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholeStrategy;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;
use Tests\Support\SensitiveSerializer\MyEvent;

class WholeStrategyTest extends StrategyTest
{
    private WholePayloadSensitizer $wholePayloadSensitizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer()
        );
    }

    /**
     * @test
     */
    public function it_should_return_original_payload_if_registry_does_not_support_event_type(): void
    {
        $wholeStrategy = new WholeStrategy(
            new WholePayloadSensitizerRegistry([]),
            $this->wholePayloadSensitizer
        );

        $sensitizedOutgoingPayload = $wholeStrategy->sensitize($this->getIngoingPayload());

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

        $wholeStrategy = new WholeStrategy(
            new WholePayloadSensitizerRegistry([MyEvent::class]),
            $this->wholePayloadSensitizer
        );

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholeStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload);
        $this->assertSensitizedPayloadEqualToExpected($sensitizedOutgoingPayload);
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

        $wholeStrategy = new WholeStrategy(
            new WholePayloadSensitizerRegistry([MyEvent::class]),
            $this->wholePayloadSensitizer
        );

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholeStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload);

        $desensitizedOutgoingPayload = $wholeStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }
}
