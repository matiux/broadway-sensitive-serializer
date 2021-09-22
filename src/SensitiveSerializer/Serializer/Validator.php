<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;

/**
 * @psalm-type SerializedObj = array{class: class-string, payload: array{id: string}}
 */
class Validator
{
    /**
     * @param array $serializedObject
     * @psalm-assert SerializedObj $serializedObject
     *
     * @throws AssertionFailedException
     */
    public static function validateSerializedObject(array $serializedObject): void
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");
        Assert::keyExists($serializedObject['payload'], 'id', "Key 'id' should be set in payload.");
    }
}
