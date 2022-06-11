<?php

declare(strict_types=1);

namespace Tests\Support\SensitiveSerializer;

use Webmozart\Assert\Assert;

class UserInfo
{
    private int $age;
    private float $height;
    private array $characteristics;

    private function __construct(int $age, float $height, array $characteristics)
    {
        $this->age = $age;
        $this->height = $height;
        $this->characteristics = $characteristics;

        Assert::notEmpty($this->characteristics, 'Provide at least one user characteristic');
    }

    /**
     * @param int          $age
     * @param float        $height
     * @param list<string> $characteristics
     *
     * @return self
     */
    public static function create(int $age, float $height, array $characteristics): self
    {
        return new self($age, $height, $characteristics);
    }

    public function age(): int
    {
        return $this->age;
    }

    public function height(): float
    {
        return $this->height;
    }

    public function characteristics(): array
    {
        return $this->characteristics;
    }
}
