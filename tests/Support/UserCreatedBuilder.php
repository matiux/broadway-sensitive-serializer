<?php

declare(strict_types=1);

namespace Tests\Support\SensitiveSerializer;

use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

class UserCreatedBuilder
{
    private string $id;
    private string $name = 'Matteo';
    private string $surname = 'Galacci';
    private string $email = 'm.galacci@gmail.com';
    private int $age = 36;
    private float $height = 1.75;
    /** @var list<string> */
    private array $characteristics = ['blonde'];
    private DateTimeRFC $occurredAt;

    private function __construct(?string $id = null)
    {
        $this->id = $id ?? '31849ba6-d2c5-45e5-bd1c-fb97276a2295';
        $this->occurredAt = new DateTimeRFC();
    }

    public static function create(?string $id = null): self
    {
        return new self($id);
    }

    public function build(): UserCreated
    {
        return new UserCreated(
            $this->id,
            $this->name,
            $this->surname,
            $this->email,
            UserInfo::create(
                $this->age,
                $this->height,
                $this->characteristics
            ),
            $this->occurredAt
        );
    }
}
