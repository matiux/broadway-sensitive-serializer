<?php

declare(strict_types=1);

namespace Test\Integration\SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy\EventStreamPartialPayloadSensitiser;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy\PartialPayloadSensitiser;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy\PartialPayloadSensitiserRegistry;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\AggregateKeyManager;
use SensitizedEventStore\Dbal\SensitiveDataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use SensitizedEventStore\Dbal\SensitiveDataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Test\Support\InMemoryAggregateKeys;
use Test\Util\Key;

class EventStreamPartialPayloadSensitiserTest extends TestCase
{
    private array $ingoingPayload = [
        'name' => 'Matteo',
        'surname' => 'Galacci',
        'email' => 'm.galacci@gmail.com',
    ];

    private UuidInterface $aggregateId;
    private DomainMessage $ingoingDomainMessage;
    private AES256SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingDomainMessage = DomainMessage::recordNow(
            (string) $this->aggregateId,
            1,
            new Metadata(),
            $this->ingoingPayload,
        );

        $this->sensitiveDataManager = new AES256SensitiveDataManager();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            new InMemoryAggregateKeys(),
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @test
     */
    public function it_should_return_original_message_if_specific_sensitiser_does_not_exist(): void
    {
        $eventStreamSensitiser = new EventStreamPartialPayloadSensitiser($this->createEmptyRegistry());
        $inputDomainEventStream = new DomainEventStream([$this->ingoingDomainMessage]);

        $outputDomainEventStream = $eventStreamSensitiser->sensitise($inputDomainEventStream);

        self::assertCount(1, $outputDomainEventStream);

        $outgoingDomainMessage = $outputDomainEventStream->getIterator()->current();
        self::assertInstanceOf(DomainMessage::class, $outgoingDomainMessage);

        $outgoingPayload = $outgoingDomainMessage->getPayload();
        self::assertIsArray($outgoingPayload);
        self::assertSame($this->ingoingPayload, $outgoingPayload);
    }

    private function createEmptyRegistry(): PartialPayloadSensitiserRegistry
    {
        return new PartialPayloadSensitiserRegistry([]);
    }

    /**
     * @test
     */
    public function it_should_return_sensitised_message_if_specific_sensitiser_exists(): void
    {
        /**
         * First let's create an AggregateKey for specific Aggregate.
         */
        $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $eventStreamSensitiser = new EventStreamPartialPayloadSensitiser($this->createRegistryWithSensitiser());
        $inputDomainEventStream = new DomainEventStream([$this->ingoingDomainMessage]);

        /**
         * Then let's sensitise message.
         */
        $sensitisedOutputDomainEventStream = $eventStreamSensitiser->sensitise($inputDomainEventStream);
        self::assertCount(1, $sensitisedOutputDomainEventStream);

        $sensitisedOutgoingDomainMessage = $sensitisedOutputDomainEventStream->getIterator()->current();
        self::assertInstanceOf(DomainMessage::class, $sensitisedOutgoingDomainMessage);

        /** @var array{name: string, surname: string, email: string} $sensitisedOutgoingPayload */
        $sensitisedOutgoingPayload = $sensitisedOutgoingDomainMessage->getPayload();

        /**
         * Finally we reveal the aggregate key and decrypt the sensitized data.
         */
        $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($this->aggregateId);

        self::assertSame('Matteo', $this->sensitiveDataManager->decrypt($sensitisedOutgoingPayload['surname'], $decryptedAggregateKey));
        self::assertSame('m.galacci@gmail.com', $this->sensitiveDataManager->decrypt($sensitisedOutgoingPayload['email'], $decryptedAggregateKey));
    }

    private function createRegistryWithSensitiser(): PartialPayloadSensitiserRegistry
    {
        return new PartialPayloadSensitiserRegistry([
            new MySensitiser($this->sensitiveDataManager, $this->aggregateKeyManager),
        ]);
    }
}

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-type P = array{name: string, surname: string, email: string}
 * @extends PartialPayloadSensitiser<P>
 */
class MySensitiser extends PartialPayloadSensitiser
{
    /**
     * @param string $decryptedAggregateKey
     *
     * @return P
     */
    protected function generateSensitisedPayload(string $decryptedAggregateKey)
    {
        [$surname, $email] = $this->sensitiseData($decryptedAggregateKey);

        return [
            'name' => $this->payload['name'],
            'surname' => $surname,
            'email' => $email,
        ];
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @return string[]
     */
    private function sensitiseData(string $decryptedAggregateKey): array
    {
        $surname = $this->sensitiveDataManager->encrypt($this->payload['name'], $decryptedAggregateKey);
        $email = $this->sensitiveDataManager->encrypt($this->payload['email'], $decryptedAggregateKey);

        return [$surname, $email];
    }

    /**
     * @param P $subject
     *
     * @return bool
     */
    public function supports($subject): bool
    {
        return true;
    }
}
