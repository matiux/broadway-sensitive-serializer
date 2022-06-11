<?php

declare(strict_types=1);

namespace Tests\Support\SensitiveSerializer;

use Broadway\Serializer\Serializable;
use Exception;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;

class UserCreated implements Serializable
{
    private string $id;
    private string $name;
    private string $surname;
    private string $email;
    private UserInfo $userInfo;
    private DateTimeRFC $occurredAt;

    public function __construct(
        string $id,
        string $name,
        string $surname,
        string $email,
        UserInfo $userInfo,
        DateTimeRFC $occurredAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->userInfo = $userInfo;
        $this->occurredAt = $occurredAt;
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @param array $data
     *
     * @throws Exception
     *
     * @return self
     */
    public static function deserialize(array $data): self
    {
        Assert::isArray($data['user_info']);
        Assert::isArray($data['user_info']['characteristics']);

        return new self(
            (string) $data['id'],
            (string) $data['name'],
            (string) $data['surname'],
            (string) $data['email'],
            UserInfo::create(
                (int) $data['user_info']['age'],
                (float) $data['user_info']['height'],
                $data['user_info']['characteristics'],
            ),
            DateTimeRFC::createFrom((string) $data['occurred_at']),
        );
    }

    /**
     * @return array{id: string, name: string, surname: string, email: string}
     */
    public function serialize(): array
    {
        return [
            'email' => $this->email,
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'user_info' => [
                'age' => $this->userInfo->age(),
                'height' => $this->userInfo->height(),
                'characteristics' => $this->userInfo->characteristics(),
            ],
            'occurred_at' => (string) $this->occurredAt,
        ];
    }
}
