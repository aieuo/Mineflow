<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

class NullVariable extends Variable implements \JsonSerializable {

    public static function getTypeName(): string {
        return "null";
    }

    public function __construct() {
        parent::__construct(null);
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
