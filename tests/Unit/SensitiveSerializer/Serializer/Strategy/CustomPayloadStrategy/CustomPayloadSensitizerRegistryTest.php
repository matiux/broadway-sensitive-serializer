<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\Strategy\CustomPayloadStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomPayloadStrategy\CustomPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\SensitiveSerializer\MyEventBuilder;

class CustomPayloadSensitizerRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_return_null_if_no_sensitizer_found_for_a_subject(): void
    {
        $event = MyEventBuilder::create()->build();
        $sensitizers = [];

        $registry = new CustomPayloadSensitizerRegistry($sensitizers);
        $sensitizer = $registry->resolveItemFor($event);

        self::assertNull($sensitizer);
    }

    /**
     * @test
     */
    public function it_should_return_sensitizer_if_subject_is_supported(): void
    {
        $event = MyEventBuilder::create()->build();
        $sensitizer = Mockery::mock(PayloadSensitizer::class)
            ->shouldReceive('supports')->andReturn(true)
            ->getMock();

        $registry = new CustomPayloadSensitizerRegistry([$sensitizer]);

        $sensitizer = $registry->resolveItemFor($event);

        self::assertNotNull($sensitizer);
        self::assertInstanceOf(PayloadSensitizer::class, $sensitizer);
    }
}