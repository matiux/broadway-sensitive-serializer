<?php

declare(strict_types=1);

namespace Test\Unit\SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy;

use Mockery;
use PHPUnit\Framework\TestCase;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy\PartialPayloadSensitiser;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy\PartialPayloadSensitiserRegistry;

class PartialPayloadSensitiserRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function should_return_null_if_no_sensitiser_found_for_a_subject(): void
    {
        $sensitisers = [];

        $registry = new PartialPayloadSensitiserRegistry($sensitisers);
        $sensitiser = $registry->resolveItemFor('{"foo": "bar"}');

        self::assertNull($sensitiser);
    }

    /**
     * @test
     */
    public function should_return_sensitiser_if_subject_is_supported(): void
    {
        $sensitiser = Mockery::mock(PartialPayloadSensitiser::class)
            ->shouldReceive('supports')->andReturn(true)
            ->getMock();

        $registry = new PartialPayloadSensitiserRegistry([$sensitiser]);

        $sensitiser = $registry->resolveItemFor('{"foo": "bar"}');

        self::assertNotNull($sensitiser);
        self::assertInstanceOf(PartialPayloadSensitiser::class, $sensitiser);
    }
}
