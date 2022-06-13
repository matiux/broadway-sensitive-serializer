<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer;

use Broadway\Serializer\SimpleInterfaceSerializer;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Serializer\SensitiveSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use PHPUnit\Framework\TestCase;
use Tests\Support\SensitiveSerializer\UserCreatedBuilder;

class SensitiveSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_use_sensitizer_when_serialize_event(): void
    {
        $event = UserCreatedBuilder::create()->build();
        $serializedEvent = [
            'class' => UserCreated::class,
            'payload' => $event->serialize(),
        ];

        $sensitizer = $this->createMock(SensitizerStrategy::class);
        $sensitizer->expects($this->exactly(1))
            ->method('sensitize')
            ->with(
                $this->equalTo($serializedEvent)
            )
            ->willReturn($serializedEvent);

        $serializer = new SensitiveSerializer(
            new SimpleInterfaceSerializer(),
            $sensitizer
        );

        $serialized = $serializer->serialize($event);

        self::assertSame($serializedEvent, $serialized);
    }

    /**
     * @test
     */
    public function it_should_use_desensitizer_when_deserialize_event(): void
    {
        $event = UserCreatedBuilder::create()->build();

        $serializedEvent = [
            'class' => UserCreated::class,
            'payload' => $event->serialize(),
        ];

        $sensitizer = $this->createMock(SensitizerStrategy::class);
        $sensitizer->expects($this->exactly(1))
            ->method('desensitize')
            ->with($this->equalTo($serializedEvent))
            ->willReturn($serializedEvent);

        $serializer = new SensitiveSerializer(
            new SimpleInterfaceSerializer(),
            $sensitizer
        );

        $deserialized = $serializer->deserialize($serializedEvent);

        self::assertEquals($event, $deserialized);
    }
}
