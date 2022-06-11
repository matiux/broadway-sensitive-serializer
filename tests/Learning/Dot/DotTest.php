<?php

declare(strict_types=1);

namespace Tests\Learning\SensitiveSerializer\Dot;

use Adbar\Dot;
use PHPUnit\Framework\TestCase;

class DotTest extends TestCase
{
    /**
     * @psalm-suppress all
     * @test
     */
    public function dot_read(): void
    {
        $array = [
            'name' => 'Matteo',
            'user_info' => [
                'age' => 36,
            ],
        ];

        $dot = new Dot();
        $dot->setReference($array);

        $age = $dot->get('user_info.age');

        self::assertSame(36, $age);

        $dot->set('user_info.age', 37);

        self::assertSame(37, $array['user_info']['age']);

        self::assertSame(37, $dot['user_info']['age']);
        self::assertSame('Matteo', $dot['name']);
        self::assertSame('Matteo', $dot->get('name'));
        self::assertTrue($dot->has('user_info.age'));
        self::assertFalse($dot->has('user_info.foo'));
        self::assertIsString($dot->get('name'));
        self::assertIsNotString($dot->get('user_info.age'));
    }

    /**
     * @test
     */
    public function dot_write(): void
    {
        $array = new Dot([]);

        $array->set('user_info.age', 36);

        $expectedArray = [
            'user_info' => [
                'age' => 36,
            ],
        ];

        self::assertEquals($expectedArray, $array->jsonSerialize());
    }
}
