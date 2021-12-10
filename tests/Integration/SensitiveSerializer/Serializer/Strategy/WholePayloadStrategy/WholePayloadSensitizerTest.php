<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\SensitiveSerializer\MyEvent;
use Tests\Support\SensitiveSerializer\MyEventBuilder;
use Tests\Util\SensitiveSerializer\Key;

class WholePayloadSensitizerTest extends TestCase
{
    use WholePayloadSensitizerTestUtil;

    private UuidInterface $aggregateId;
    private array $ingoingPayload;

    private InMemoryAggregateKeys $aggregateKeys;
    private AES256SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingPayload = [
            'class' => MyEvent::class,
            'payload' => MyEventBuilder::create((string) $this->aggregateId)->build()->serialize(),
        ];

        $this->aggregateKeys = new InMemoryAggregateKeys();
        $this->sensitiveDataManager = new AES256SensitiveDataManager();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_support_method_called(): void
    {
        self::expectException(BadMethodCallException::class);

        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->sensitiveDataManager,
            $this->aggregateKeyManager
        );

        $wholePayloadSensitizer->supports([]);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_aggregate_key_id_missing_during_encryption(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->aggregateId));

        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->sensitiveDataManager,
            $this->aggregateKeyManager,
            false
        );

        $wholePayloadSensitizer->sensitize($this->ingoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_whole_sensitized_array(): void
    {
        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->sensitiveDataManager,
            $this->aggregateKeyManager
        );

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizer->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);
        $this->assertSensitizedEqualToExpected($sensitizedOutgoingPayload);
    }

    /**
     * @test
     */
    public function it_should_return_desensitized_array(): void
    {
        $wholePayloadSensitizer = new WholePayloadSensitizer(
            $this->sensitiveDataManager,
            $this->aggregateKeyManager
        );

        /**
         * First let's sensitize message.
         *
         * @var array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
         */
        $sensitizedOutgoingPayload = $wholePayloadSensitizer->sensitize($this->ingoingPayload);

        self::assertObjectIsSensitized($sensitizedOutgoingPayload);

        $desensitizedOutgoingPayload = $wholePayloadSensitizer->desensitize($sensitizedOutgoingPayload);

        self::assertSame($this->ingoingPayload, $desensitizedOutgoingPayload);
    }
}
