<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizerStrategy;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\InMemoryAggregateKeys;
use Tests\Support\MyEvent;
use Tests\Support\MyEventBuilder;
use Tests\Util\Key;

class WholePayloadSensitizerStrategyTest extends TestCase
{
    use WholePayloadSensitizerTestUtil;

    private UuidInterface $aggregateId;
    private array $ingoingPayload;

    private AES256SensitiveDataManager $sensitiveDataManager;
    private InMemoryAggregateKeys $aggregateKeys;
    private AggregateKeyManager $aggregateKeyManager;
    private WholePayloadSensitizer $wholePayloadSensitizer;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingPayload = [
            'class' => MyEvent::class,
            'payload' => MyEventBuilder::create((string) $this->aggregateId)->build()->serialize(),
        ];

        $this->sensitiveDataManager = new AES256SensitiveDataManager();
        $this->aggregateKeys = new InMemoryAggregateKeys();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );

        $this->wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->sensitiveDataManager,
            $this->aggregateKeyManager
        );
    }

    /**
     * @test
     */
    public function it_should_return_original_payload_if_registry_does_not_support_event_type(): void
    {
        $wholePayloadSensitizerStrategy = new WholePayloadSensitizerStrategy(
            new WholePayloadSensitizerRegistry([]),
            $this->wholePayloadSensitizer
        );

        $sensitizedOutgoingPayload = $wholePayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        self::assertSame($this->ingoingPayload, $sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_sensitize_payload_if_registry_supports_event_type(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $wholePayloadSensitizerStrategy = new WholePayloadSensitizerStrategy(
            new WholePayloadSensitizerRegistry([MyEvent::class]),
            $this->wholePayloadSensitizer
        );

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);
        $this->assertSensitizedEqualToExpected($sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_desensitize_payload_if_registry_supports_event_type(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $wholePayloadSensitizerStrategy = new WholePayloadSensitizerStrategy(
            new WholePayloadSensitizerRegistry([MyEvent::class]),
            $this->wholePayloadSensitizer
        );

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizerStrategy->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);

        $desensitizedOutgoingPayload = $wholePayloadSensitizerStrategy->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->ingoingPayload, $desensitizedOutgoingPayload);
    }
}
