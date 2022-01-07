<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

final class PartialPayloadSensitizerRegistry
{
    /** @var array<class-string, PayloadSensitizer> */
    private array $items = [];

    /**
     * @param PayloadSensitizer[] $items
     */
    public function __construct(iterable $items)
    {
        foreach ($items as $item) {
            $name = get_class($item);

            if (!isset($this->items[$name])) {
                $this->items[$name] = $item;
            }
        }
    }

    /**
     * @template T of \Broadway\Serializer\Serializable
     *
     * @param array|T $subject
     *
     * @return null|PayloadSensitizer
     */
    public function resolveItemFor($subject): ?PayloadSensitizer
    {
        foreach ($this->items as $item) {
            if ($item->supports($subject)) {
                return $item;
            }
        }

        return null;
    }
}
