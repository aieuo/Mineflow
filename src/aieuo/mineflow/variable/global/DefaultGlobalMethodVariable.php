<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\global;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\VariableMethod;
use function mt_getrandmax;
use function mt_rand;
use const M_PI;

class DefaultGlobalMethodVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "global";
    }

    public function getValue(): mixed {
        return null;
    }

    public function __toString(): string {
        return (string)(new MapVariable(self::getMethodTypes()));
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "random", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn($_ = null, $min = null, $max = null) => new NumberVariable(mt_rand($min === null ? $min : (int)$min, $max === null ? $max : (int)$max)),
        ),  aliases: ["rand_int"]);
        self::registerMethod($class, "frandom", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn($_ = null) => new NumberVariable(mt_rand() / mt_getrandmax()),
        ), aliases: ["rand_float"]);
        self::registerMethod($class, "pi", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn($_ = null) => M_PI,
        ));
        self::registerMethod($class, "vars", new VariableMethod(
            new DummyVariable(MapVariable::class),
            fn($_ = null) => new MapVariable(VariableRegistry::global()->getAll()),
        ));
    }
}