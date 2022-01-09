<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;

/**
 * @psalm-immutable
 */
class DateTimeRFC extends DateTimeImmutable
{
    public function __toString(): string
    {
        return $this->format(DateTimeInterface::RFC3339_EXTENDED);
    }

    /**
     * Quick and very dirty.
     *
     * @param string $dateTime
     *
     * @throws Exception
     *
     * @return DateTimeRFC
     */
    public static function createFrom(string $dateTime): DateTimeRFC
    {
        $date = self::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $dateTime);

        return new self($date->format(DateTimeInterface::RFC3339_EXTENDED));
    }
}
