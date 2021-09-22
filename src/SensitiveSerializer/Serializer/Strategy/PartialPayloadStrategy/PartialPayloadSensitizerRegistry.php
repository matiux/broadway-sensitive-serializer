<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

class PartialPayloadSensitizerRegistry
{
    /** @var array<class-string, PartialPayloadSensitizer> */
    private array $items = [];

    /**
     * @param PartialPayloadSensitizer[] $items
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
     * @return null|PartialPayloadSensitizer
     */
    public function resolveItemFor($subject): ?PartialPayloadSensitizer
    {
        foreach ($this->items as $item) {
            if ($item->supports($subject)) {
                return $item;
            }
        }

        return null;
    }
}
