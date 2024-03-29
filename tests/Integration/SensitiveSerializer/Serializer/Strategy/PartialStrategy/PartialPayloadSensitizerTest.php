<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;

class PartialPayloadSensitizerTest extends StrategyTest
{
    use PartialStrategyTestUtil;

    /**
     * @test
     */
    public function it_should_throw_exception_if_support_method_called(): void
    {
        self::expectException(\BadMethodCallException::class);

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry([]));

        $partialPayloadSensitizer->supports([]);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->getUserId()));

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry([]), false);

        $partialPayloadSensitizer->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_return_sensitized_array(): void
    {
        $events = [
            UserCreated::class => ['email', 'surname', 'user_info.age', 'user_info.characteristics'],
        ];

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry($events));

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, user_info: array}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['email', 'surname', 'user_info.age', 'user_info.characteristics']);

        self::assertArrayHasKey('user_info', $sensitizedOutgoingPayload['payload']);
        self::assertArrayHasKey('height', $sensitizedOutgoingPayload['payload']['user_info']);
        self::assertSame(1.75, $sensitizedOutgoingPayload['payload']['user_info']['height']);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array(): void
    {
        $events = [
            UserCreated::class => ['email', 'surname', 'user_info.characteristics'],
        ];

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry($events));

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $partialPayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, ['email', 'surname', 'user_info.characteristics']);

        $desensitizedOutgoingPayload = $partialPayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_registry_does_not_resolve(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage(sprintf('If you are here, the strategy should have identified correct event in registry: %s', UserCreated::class));

        $sensitizer = new PartialPayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer(),
            new PartialPayloadSensitizerRegistry([]),
        );

        $sensitizer->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_try_to_serialize_associative_array(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Expected a array<int, mixed>|scalar|null. Got: array');

        $events = [
            UserCreated::class => ['user_info'],
        ];

        $partialPayloadSensitizer = $this->buildPartialPayloadSensitizer(new PartialPayloadSensitizerRegistry($events));

        /**
         * First let's sensitize message.
         */
        $partialPayloadSensitizer->sensitize($this->getIngoingPayload());
    }
}
