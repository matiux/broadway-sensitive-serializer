<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate;

use Exception;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Take a look at the matiux/ddd-starter-pack:v3 library for more info
 */
abstract class BasicEntityId
{
    public const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    /** @var null|int|string */
    private $id;

    final protected function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return !$this->id ? '' : (string) $this->id;
    }

    /**
     * @throws Exception
     *
     * @return static
     */
    public static function create(): self
    {
        return new static(Uuid::uuid4()->toString());
    }

    /**
     * @param int|string $id
     *
     * @return static
     */
    public static function createFrom($id): self
    {
        if (!$id) {
            throw new InvalidArgumentException(sprintf('Invalid ID: %s', $id));
        }

        return new static($id);
    }

    public static function createNUll(): self
    {
        return new static(null);
    }

    public static function isValidUuid(string $uuid): bool
    {
        if (1 === preg_match(self::UUID_PATTERN, $uuid)) {
            return true;
        }

        return false;
    }

    public function equals(BasicEntityId $entityId): bool
    {
        return $this->id() === $entityId->id();
    }

    /**
     * @return null|int|string
     */
    public function id()
    {
        return $this->id;
    }

    public function isNull(): bool
    {
        return is_null($this->id);
    }
}
