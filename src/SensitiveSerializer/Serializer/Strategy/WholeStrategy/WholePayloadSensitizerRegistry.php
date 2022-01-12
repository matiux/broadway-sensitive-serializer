<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use Exception;

final class WholePayloadSensitizerRegistry
{
    /** @var class-string[] */
    private array $supportedEvents = [];

    /**
     * @param class-string[] $supportedEvents
     *
     * @throws Exception
     */
    public function __construct(iterable $supportedEvents)
    {
        foreach ($supportedEvents as $eventName) {
            $this->isValidClassString($eventName);

            if (!isset($this->supportedEvents[$eventName])) {
                $this->supportedEvents[$eventName] = $eventName;
            }
        }
    }

    /**
     * @param class-string $eventName
     *
     *@throws Exception
     *
     * @return bool
     */
    public function supports(string $eventName): bool
    {
        $this->isValidClassString($eventName);

        return array_key_exists($eventName, $this->supportedEvents);
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
