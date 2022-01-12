<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomStrategy;

use Exception;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

final class CustomPayloadSensitizerRegistry
{
    /** @var array<class-string, PayloadSensitizer> */
    private array $items = [];

    /**
     * @param PayloadSensitizer[] $items
     *
     * @throws Exception
     */
    public function __construct(iterable $items)
    {
        foreach ($items as $item) {
            $this->isValidPayloadSensitizer($item);

            $name = get_class($item);

            if (!isset($this->items[$name])) {
                $this->items[$name] = $item;
            }
        }
    }

    /**
     * @param PayloadSensitizer $sensitizer
     *
     * @throws Exception
     */
    private function isValidPayloadSensitizer($sensitizer): void
    {
        if (!is_subclass_of($sensitizer, PayloadSensitizer::class)) {
            throw new Exception(sprintf('Sensitizer must implements: %s. Given %s', PayloadSensitizer::class, get_class($sensitizer)));
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
