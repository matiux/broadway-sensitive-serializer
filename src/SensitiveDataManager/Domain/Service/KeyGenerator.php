<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service;

interface KeyGenerator
{
    public function generate(): string;
}
