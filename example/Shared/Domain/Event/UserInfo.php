<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Webmozart\Assert\Assert;

class UserInfo
{
    /** @var int|string */
    private $age;

    /** @var float|string */
    private $height;

    /** @var list<string> */
    private array $characteristics;

    private function __construct($age, $height, array $characteristics)
    {
        $this->age = $age;
        $this->height = $height;
        $this->characteristics = $characteristics;

        if (!SensitiveTool::isSensitized($age)) {
            Assert::greaterThan($age, 0);
        }

        if (!SensitiveTool::isSensitized($height)) {
            Assert::greaterThan($height, 0);
        }

        Assert::notEmpty($this->characteristics, 'Provide at least one user characteristic');
    }

    /**
     * @param int|string   $age
     * @param float|string $height
     * @param list<string> $characteristics
     *
     * @return self
     */
    public static function create($age, $height, array $characteristics): self
    {
        return new self($age, $height, $characteristics);
    }

    public function age()
    {
        return $this->age;
    }

    public function height()
    {
        return $this->height;
    }

    public function characteristics(): array
    {
        return $this->characteristics;
    }
}
