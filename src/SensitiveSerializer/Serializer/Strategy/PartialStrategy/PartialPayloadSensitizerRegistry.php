<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Webmozart\Assert\Assert;

final class PartialPayloadSensitizerRegistry
{
    /** @var array<class-string, string[]> */
    private array $supportedEvents = [];

    /**
     * @param array<class-string, string[]> $items
     *
     * @throws \Exception
     */
    public function __construct(iterable $items)
    {
        foreach ($items as $eventName => $toSensitizeKeysList) {
            $this->isValidClassString($eventName);
            $this->isValidToSensitizeKeysList($toSensitizeKeysList);

            if (!isset($this->supportedEvents[$eventName])) {
                $this->supportedEvents[$eventName] = $toSensitizeKeysList;
            }
        }
    }

    /**
     * @param string $classString
     *
     * @throws \Exception
     */
    private function isValidClassString(string $classString): void
    {
        if (!class_exists($classString)) {
            throw new \Exception(sprintf('Invalid class string: %s', $classString));
        }
    }

    /**
     * @param string[] $toSensitizeKeysList
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    private function isValidToSensitizeKeysList(array $toSensitizeKeysList): void
    {
        Assert::notEmpty($toSensitizeKeysList, 'List of keys to sensitize cannot be empty');

        foreach ($toSensitizeKeysList as $key) {
            Assert::string($key, sprintf('PartialPayloadSensitizer needs a list of string keys. `%s` is not a string', $key));
            Assert::notEmpty($key, 'Invalid empty string in the list of keys to sensitize');
            Assert::notContains($key, ' ', sprintf('PartialPayloadSensitizer needs a list of string keys. `%s` is not a valid string', $key));
        }
    }

    /**
     * @param class-string $eventName
     *
     * @throws \Exception
     *
     * @return null|string[]
     */
    public function resolveItemFor(string $eventName): ?array
    {
        if ($this->support($eventName)) {
            return $this->supportedEvents[$eventName];
        }

        return null;
    }

    public function support(string $eventName): bool
    {
        return array_key_exists($eventName, $this->supportedEvents);
    }
}
