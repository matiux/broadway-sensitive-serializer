<?php

declare(strict_types=1);

namespace Tests\Learning\SensitiveSerializer\Misc;

use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Util;
use PHPUnit\Framework\TestCase;
use Tests\Support\SensitiveSerializer\UserCreatedBuilder;

/**
 * @psalm-suppress all
 */
class MiscTest extends TestCase
{
    /**
     * @test
     */
    public function handle_associative_array_recursively(): void
    {
        $serializedEvent = UserCreatedBuilder::create()->build()->serialize();

        $this->recursiveFunction($serializedEvent);

        self::assertEachElementHasBeenTouched($serializedEvent);
    }

    private function recursiveFunction(array &$array): void
    {
        foreach ($array as &$value) {
            if (!Util::isAssociativeArray($value)) {
                $value .= '++';
            } else {
                $this->recursiveFunction($value);
            }
        }
    }

    private static function assertEachElementHasBeenTouched(array $serializedEvent): void
    {
        foreach ($serializedEvent as $value) {
            if (!Util::isAssociativeArray($value)) {
                self::assertStringEndsWith('++', $value);
            } else {
                self::assertEachElementHasBeenTouched($value);
            }
        }
    }
}
