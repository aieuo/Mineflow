<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;

class UnknownObjectVariable extends ObjectVariable {

    public function __construct() {
        parent::__construct(null, "unknown");
    }

    public static function getTypeName(): string {
        return "unknown";
    }

    public function getProperty(string $name): ?Variable {
        return null;
    }

    public static function getValuesDummy(): array {
        return [];
    }

    public function __toString(): string {
        return "unknown";
    }
}