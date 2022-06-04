<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate;

use InvalidArgumentException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;

class Email
{
    private string $email;

    private function __construct(string $email)
    {
        return $this->email = $email;
    }

    public static function createFromString(string $email): self
    {
        if (!SensitiveTool::isSensitized($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException(sprintf('Invalid email: %s', $email));
            }
        }

        return new self($email);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
