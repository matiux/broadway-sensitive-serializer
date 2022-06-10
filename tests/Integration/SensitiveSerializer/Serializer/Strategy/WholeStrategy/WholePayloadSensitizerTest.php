<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholePayloadSensitizer;
use Tests\Integration\SensitiveSerializer\Serializer\Strategy\StrategyTest;

class WholePayloadSensitizerTest extends StrategyTest
{
    /**
     * @test
     */
    public function it_should_throw_exception_if_support_method_called(): void
    {
        self::expectException(BadMethodCallException::class);

        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer()
        );

        $wholePayloadSensitizer->supports([]);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->getAggregateId()));

        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer(),
            false
        );

        $wholePayloadSensitizer->sensitize($this->getIngoingPayload());
    }

    /**
     * @test
     */
    public function it_should_return_whole_sensitized_array(): void
    {
        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer()
        );

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload);
        $this->assertSensitizedPayloadEqualToExpected($sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array(): void
    {
        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer()
        );

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizer->sensitize($this->getIngoingPayload());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload);

        $desensitizedOutgoingPayload = $wholePayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_exclude_specific_keys_from_sensitization(): void
    {
        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->getSensitiveDataManager(),
            $this->getAggregateKeyManager(),
            $this->getValueSerializer(),
            true,
            ['name']
        );

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, name: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizer->sensitize($this->getIngoingPayload());

        $excludedKeys = array_merge(['id'], $wholePayloadSensitizer->excludedKeys());

        $this->assertObjectIsSensitized($sensitizedOutgoingPayload, $excludedKeys);

        self::assertFalse(SensitiveTool::isSensitized($sensitizedOutgoingPayload['payload']['name']));

        $desensitizedOutgoingPayload = $wholePayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->getIngoingPayload(), $desensitizedOutgoingPayload);
    }
}
