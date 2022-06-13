<?php

declare(strict_types=1);

namespace Tests\Support\SensitiveSerializer;

use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\Email;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserInfo;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

class UserCreatedBuilder
{
    private UserId $id;
    private string $name = 'Matteo';
    private string $surname = 'Galacci';
    private Email $email;
    private int $age = 36;
    private float $height = 1.75;
    /** @var list<string> */
    private array $characteristics = ['blonde'];
    private DateTimeRFC $occurredAt;

    private function __construct(?UserId $id = null)
    {
        $this->id = $id ?? UserId::create();
        $this->email = Email::createFromString('m.galacci@gmail.com');
        $this->occurredAt = new DateTimeRFC();
    }

    public static function create(?UserId $id = null): self
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
