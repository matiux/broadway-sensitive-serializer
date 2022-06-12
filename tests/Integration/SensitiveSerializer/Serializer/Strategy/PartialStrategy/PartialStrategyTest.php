<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use Ramsey\Uuid\Uuid;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;

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
        $this->getAggregateKeyManager()->createAggregateKey(Uuid::fromString((string) $this->getUserId()));

        $partialStrategy = $this->buildPartialStrategy(new PartialPayloadSensitizerRegistry([UserCreated::class => ['surname', 'email', 'user_info.height']]));

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['surname', 'email', 'user_info.height']);
//        $this->assertSensitizedPayloadEqualToExpected($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);
    }

    /**
     * @test
     */
    public function it_should_desensitize_payload_if_registry_supports_event_type(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->getAggregateKeyManager()->createAggregateKey(Uuid::fromString((string) $this->getUserId()));

        $partialStrategy = $this->buildPartialStrategy(new PartialPayloadSensitizerRegistry([UserCreated::class => ['surname', 'email']]));

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialStrategy->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['surname', 'email']);

        $desensitizedOutgoingPayload = $partialStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }
}
