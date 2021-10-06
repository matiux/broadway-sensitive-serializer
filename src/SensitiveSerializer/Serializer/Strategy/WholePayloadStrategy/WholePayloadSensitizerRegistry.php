<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Exception;

class WholePayloadSensitizerRegistry
{
    /** @var class-string[] */
    private array $items = [];

    /**
     * @param class-string[] $items
     *
     * @throws Exception
     */
    public function __construct(iterable $items)
    {
        foreach ($items as $classString) {
            $this->isValidClassString($classString);

            if (!isset($this->items[$classString])) {
                $this->items[$classString] = $classString;
            }
        }
    }

    /**
     * @param class-string $classString
     *
     * @throws Exception
     *
     * @return bool
     */
    public function supports(string $classString): bool
    {
        $this->isValidClassString($classString);

        return array_key_exists($classString, $this->items);
    }

    /**
     * @param string $classString
     *
     * @throws Exception
     */
    private function isValidClassString(string $classString): void
    {
        if (!class_exists($classString)) {
            throw new Exception(sprintf('Invalid class string: %s', $classString));
        }
    }
}
