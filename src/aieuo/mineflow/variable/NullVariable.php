<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

class NullVariable extends Variable implements \JsonSerializable {

    public static function getTypeName(): string {
        return "null";
    }

    public function getValue(): mixed {
        return null;
    }

    public function __toString(): string {
        return "null";
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => null,
        ];
    }
}