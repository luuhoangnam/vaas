<?php

namespace App\Support;

use ReflectionClass;

abstract class Enum
{
    public static function values(): array
    {
        $reflection = new ReflectionClass(static::class);

        return $reflection->getConstants();
    }

    public static function isValid($name): bool
    {
        return in_array($name, static::values());
    }
}