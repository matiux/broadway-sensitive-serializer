<?php

declare(strict_types=1);

namespace Tests\Support\SensitiveSerializer;

class MyEventBuilder
{
    private string $id;
    private string $name = 'Matteo';
    private string $surname = 'Galacci';
    private string $email = 'm.galacci@gmail.com';

    private function __construct(?string $id = null)
    {
        $this->id = $id ?? '31849ba6-d2c5-45e5-bd1c-fb97276a2295';
    }

    public static function create(?string $id = null): self
    {
        return new self($id);
    }

    public function build(): MyEvent
    {
        return new MyEvent(
            $this->id,
            $this->name,
            $this->surname,
            $this->email
        );
    }
}
