<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject;

use DateTimeImmutable;
use Exception;

/**
 * @psalm-immutable
 */
class DateTimeRFC extends DateTimeImmutable
{
    /**
     * Based on DateTimeInterface::RFC3339_EXTENDED = Y-m-d\TH:i:s.vP
     * v = Milliseconds
     * u = Microseconds
     */
    public const FORMAT = 'Y-m-d\TH:i:s.uP';

    public function __toString(): string
    {
        return $this->format(self::FORMAT);
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
        $date = self::createFromFormat(self::FORMAT, $dateTime);

        return new self($date->format(self::FORMAT));
    }
}
