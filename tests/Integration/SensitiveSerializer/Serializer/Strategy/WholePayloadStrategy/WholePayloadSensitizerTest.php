<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\InMemoryAggregateKeys;
use Tests\Support\MyEvent;
use Tests\Support\MyEventBuilder;
use Tests\Util\Key;

class WholePayloadSensitizerTest extends TestCase
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
    public function it_should_throw_exception_if_support_method_called(): void
    {
        self::expectException(BadMethodCallException::class);

        $this->wholePayloadSensitizer->supports([]);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->aggregateId));

        $this->wholePayloadSensitizer->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_whole_sensitized_array(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $this->wholePayloadSensitizer->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);
        $this->assertSensitizedEqualToExpected($sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        /**
         * Then let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $this->wholePayloadSensitizer->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);

        $desensitizedOutgoingPayload = $this->wholePayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->ingoingPayload, $desensitizedOutgoingPayload);
    }
}
