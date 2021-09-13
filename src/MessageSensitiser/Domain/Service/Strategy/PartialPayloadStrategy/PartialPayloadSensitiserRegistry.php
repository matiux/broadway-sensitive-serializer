<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy;

/**
 * @template S
 */
class PartialPayloadSensitiserRegistry
{
    /** @var array<class-string, PartialPayloadSensitiser> */
    private array $items = [];

    /**
     * @param PartialPayloadSensitiser[] $items
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
     * @param S $subject
     *
     * @return null|PartialPayloadSensitiser
     */
    public function resolveItemFor($subject): ?PartialPayloadSensitiser
    {
        foreach ($this->items as $item) {
            if ($item->supports($subject)) {
                return $item;
            }
        }

        return null;
    }
}
