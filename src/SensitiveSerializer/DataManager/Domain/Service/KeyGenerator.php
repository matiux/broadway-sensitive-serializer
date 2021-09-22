<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

interface KeyGenerator
{
    public function generate(): string;
}
