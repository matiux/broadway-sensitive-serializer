<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\CustomStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserRegistered;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

class UserRegisteredSensitizer extends PayloadSensitizer
{
    /**
     * @throws AssertionFailedException
     */
    protected function generateSensitizedPayload(): array
    {
        $this->validatePayload($this->getPayload());

        $email = $this->encryptValue($this->getPayload()['email']);

        $payload = $this->getPayload();
        $payload['email'] = $email;

        return $payload;
    }

    /**
     * @throws AssertionFailedException
     */
    protected function generateDesensitizedPayload(): array
    {
        $this->validatePayload($this->getPayload());

        $email = $this->decryptValue($this->getPayload()['email']);

        $payload = $this->getPayload();
        $payload['email'] = $email;

        return $payload;
    }

    public function supports($subject): bool
    {
        return UserRegistered::class == $subject['class'];
    }

    /**
     * @psalm-assert MyEvent $payload
     *
     * @throws AssertionFailedException
     */
    protected function validatePayload(array $payload): void
    {
        Assert::keyExists($payload, 'email', "Key 'email' should be set in payload.");
    }
}
