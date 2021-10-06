<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy;

interface SensitizerStrategy
{
    /**
     * @param array $serializedObject
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array;

    /**
     * @param array $sensitiveSerializedObject
     *
     * @return array
     */
    public function desensitize(array $sensitiveSerializedObject): array;
}
