<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\Strategy\CustomStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomStrategy\CustomPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Support\SensitiveSerializer\UserCreatedBuilder;

class CustomPayloadSensitizerRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_return_null_if_no_sensitizer_found_for_a_subject(): void
    {
        $event = UserCreatedBuilder::create()->build();
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
        $event = UserCreatedBuilder::create()->build();
        $sensitizer = Mockery::mock(PayloadSensitizer::class)
            ->shouldReceive('supports')->andReturn(true)
            ->getMock();

        $registry = new CustomPayloadSensitizerRegistry([$sensitizer]);

        $sensitizer = $registry->resolveItemFor($event);

        self::assertNotNull($sensitizer);
        self::assertInstanceOf(PayloadSensitizer::class, $sensitizer);
    }

    /**
     * @test
     * @psalm-suppress InvalidArgument
     */
    public function it_should_throw_exception_if_items_passed_to_registry_is_invalid(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage(sprintf('Sensitizer must implements: %s. Given %s', PayloadSensitizer::class, get_class(new \stdClass())));

        $sensitizer = [
            new \stdClass(),
        ];

        new CustomPayloadSensitizerRegistry($sensitizer);
    }
}
