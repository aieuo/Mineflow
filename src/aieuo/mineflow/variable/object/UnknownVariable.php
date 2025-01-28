<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;

class UnknownVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "unknown";
    }

    public function getValue(): mixed {
        return null;
    }

    public function __toString(): string {
        return "unknown";
    }

    public static function registerProperties(string $class = self::class): void {
    }
}