<?php

declare(strict_types=1);

namespace Test\Unit\SensitizedEventStore\Dbal\MessageSensitiser\Domain\Aggregate;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStore;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Aggregate\SensitiserEventStoreDecorator;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\EventStreamSensitiserStrategy;

class SensitiserEventStoreDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_use_desensitiser_when_load_aggregate(): void
    {
        $id = Uuid::uuid4();
        $des = new DomainEventStream([]);

        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects($this->exactly(1))
            ->method('load')
            ->with(
                $this->equalTo($id)
            )
            ->willReturn($des);

        $eventStreamSensitiserStrategy = $this->createMock(EventStreamSensitiserStrategy::class);
        $eventStreamSensitiserStrategy->expects($this->exactly(1))
            ->method('desensitise')
            ->with(
                $this->equalTo($des)
            )
            ->willReturn($des);

        $sensitiserEventStoreDecorator = new SensitiserEventStoreDecorator($eventStore, $eventStreamSensitiserStrategy);

        $sensitiserEventStoreDecorator->load($id);
    }

    /**
     * @test
     */
    public function it_should_use_sensitiser_when_append_aggregate(): void
    {
        $id = Uuid::uuid4();
        $des = new DomainEventStream([]);

        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects($this->exactly(1))
            ->method('append')
            ->with(
                $this->equalTo($id),
                $this->equalTo($des),
            );

        $eventStreamSensitiserStrategy = $this->createMock(EventStreamSensitiserStrategy::class);
        $eventStreamSensitiserStrategy->expects($this->exactly(1))
            ->method('sensitise')
            ->with($des)
            ->willReturn($des);

        $sensitiserEventStoreDecorator = new SensitiserEventStoreDecorator($eventStore, $eventStreamSensitiserStrategy);

        $sensitiserEventStoreDecorator->append($id, $des);
    }
}
