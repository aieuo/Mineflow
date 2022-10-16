<?php

declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\variable\object\Vector3ObjectVariable;
use function array_map;

class VariableSerializer {

    /** @var array<string, callable(Variable): mixed> */
    private static array $serializers = [];
    /** @var array<class-string<Variable>, string> */
    private static array $types;

    public static function init(): void {
        self::register(StringVariable::class, Variable::STRING, static fn(StringVariable $var) => $var->getValue());
        self::register(NumberVariable::class, Variable::NUMBER, static fn(NumberVariable $var) => $var->getValue());
        self::register(BoolVariable::class, Variable::BOOLEAN, static fn(BoolVariable $var) => $var->getValue());
        self::register(NullVariable::class, Variable::NULL, static fn() => null);
        self::register(ListVariable::class, Variable::LIST, static function (ListVariable $var) {
            return array_map(fn(Variable $v) => self::serialize($v) ?? self::fallback($v), $var->getValue());
        });
        self::register(MapVariable::class, (string)Variable::MAP, static function (MapVariable $var) {
            return array_map(fn(Variable $v) => self::serialize($v) ?? self::fallback($v), $var->getValue());
        });

        self::register(ItemObjectVariable::class, "item", static fn(ItemObjectVariable $var) => $var->getItem()->jsonSerialize());
        self::register(Vector3ObjectVariable::class, "vector3", static fn(Vector3ObjectVariable $var) => [
            "x" => $var->getVector3()->getX(),
            "y" => $var->getVector3()->getY(),
            "z" => $var->getVector3()->getZ(),
        ]);
        self::register(PositionObjectVariable::class, "position", static fn(PositionObjectVariable $var) => [
            "x" => $var->getPosition()->getX(),
            "y" => $var->getPosition()->getY(),
            "z" => $var->getPosition()->getZ(),
            "world" => $var->getPosition()->getWorld()->getFolderName(),
        ]);
        self::register(LocationObjectVariable::class, "location", static fn(LocationObjectVariable $var) => [
            "x" => $var->getLocation()->getX(),
            "y" => $var->getLocation()->getY(),
            "z" => $var->getLocation()->getZ(),
            "world" => $var->getLocation()->getWorld()->getFolderName(),
            "yaw" => $var->getLocation()->getYaw(),
            "pitch" => $var->getLocation()->getPitch(),
        ]);
    }

    /**
     * @param string $class
     * @param string|int $type
     * @param callable(array<string, mixed>): ?Variable $deserializer
     * @param bool $override
     * @return void
     */
    public static function register(string $class, string|int $type, callable $deserializer, bool $override = false): void {
        if (!$override and isset(self::$serializers[$type])) {
            throw new \InvalidArgumentException("Variable serializer ".$type." is already registered");
        }

        self::$types[$class] = $type;
        self::$serializers[$type] = $deserializer;
    }

    public static function serialize(Variable $variable): ?array {
        if (!isset(self::$types[$variable::class])) return null;
        $type = self::$types[$variable::class];

        return [
            "type" => $type,
            "value" => (self::$serializers[$type])($variable),
        ];
    }

    public static function fallback(Variable $variable): array {
        return [
            "type" => Variable::STRING,
            "value" => (string)$variable,
        ];
    }
}
