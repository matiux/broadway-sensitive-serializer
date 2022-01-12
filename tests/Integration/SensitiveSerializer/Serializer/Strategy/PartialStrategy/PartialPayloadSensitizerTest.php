<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use BadMethodCallException;
use LogicException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;
use Tests\Support\SensitiveSerializer\MyEvent;

class PartialPayloadSensitizerTest extends StrategyTest
{
    use PartialStrategyTestUtil;

    /**
     * @test
     */
    public function it_should_throw_exception_if_support_method_called(): void
    {
        self::expectException(BadMethodCallException::class);

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry([]));

        $partialPayloadSensitizer->supports([]);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->getAggregateId()));

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry([]), false);

        $partialPayloadSensitizer->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_return_sensitized_array(): void
    {
        $events = [
            MyEvent::class => ['email', 'surname'],
        ];

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry($events));

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);
        $this->assertSensitizedEqualToExpected($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array(): void
    {
        $events = [
            MyEvent::class => ['email', 'surname'],
        ];

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry($events));

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['id', 'name', 'occurred_at']);

        $desensitizedOutgoingPayload = $partialPayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_registry_does_not_resolve(): void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage(sprintf('If you are here, the strategy should have identified correct event in registry: %s', MyEvent::class));

        $sensitizer = new PartialPayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            new PartialPayloadSensitizerRegistry([]),
        );

        $sensitizer->sensitize($this->getIngoingPayload());
    }
}
