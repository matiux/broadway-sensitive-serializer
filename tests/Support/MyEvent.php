<?php

declare(strict_types=1);

namespace Tests\Support;

use Broadway\Serializer\Serializable;

class MyEvent implements Serializable
{
    private string $id;
    private string $name;
    private string $surname;
    private string $email;

    public function __construct(string $id, string $name, string $surname, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
    }

    public static function deserialize(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['name'],
            (string) $data['surname'],
            (string) $data['email'],
        );
    }

    /**
     * @return array{id: string, name: string, surname: string, email: string}
     */
    public function serialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
        ];
    }
}
