<?php

declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\entity\Location;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\world\Position;
use function array_map;
use function base64_encode;

class VariableSerializer {

    /** @var array<string, callable(Variable): mixed> */
    private static array $serializers = [];

    public static function init(): void {
        self::register(StringVariable::getTypeName(), static fn(StringVariable $var) => $var->getValue());
        self::register(NumberVariable::getTypeName(), static fn(NumberVariable $var) => $var->getValue());
        self::register(BooleanVariable::getTypeName(), static fn(BooleanVariable $var) => $var->getValue());
        self::register(NullVariable::getTypeName(), static fn() => null);
        self::register(ListVariable::getTypeName(), static function (ListVariable $var) {
            return array_map(fn(Variable $v) => self::serialize($v) ?? self::fallback($v), $var->getValue());
        });
        self::register(MapVariable::getTypeName(), static function (MapVariable $var) {
            return array_map(fn(Variable $v) => self::serialize($v) ?? self::fallback($v), $var->getValue());
        });

        self::register(ItemVariable::getTypeName(), static function(ItemVariable $var) {
            $nbt = $var->getValue()->nbtSerialize();
            return base64_encode((new BigEndianNbtSerializer())->write(new TreeRoot($nbt)));
        });
        self::register(Vector3Variable::getTypeName(), static fn(Vector3Variable $var) => [
            "x" => $var->getValue()->getX(),
            "y" => $var->getValue()->getY(),
            "z" => $var->getValue()->getZ(),
        ]);
        self::register(PositionVariable::getTypeName(), static function (PositionVariable $var) {
            /** @var Position $pos */
            $pos = $var->getValue();
            return [
                "x" => $pos->getX(),
                "y" => $pos->getY(),
                "z" => $pos->getZ(),
                "world" => $pos->getWorld()->getFolderName(),
            ];
        });
        self::register(LocationVariable::getTypeName(), static function (LocationVariable $var) {
            /** @var Location $pos */
            $pos = $var->getValue();
            return [
                "x" => $pos->getX(),
                "y" => $pos->getY(),
                "z" => $pos->getZ(),
                "world" => $pos->getWorld()->getFolderName(),
                "yaw" => $pos->getYaw(),
                "pitch" => $pos->getPitch(),
            ];
        });
    }

    /**
     * @param string $type
     * @param callable(Variable): mixed $serializer
     * @param bool $override
     * @return void
     */
    public static function register(string $type, callable $serializer, bool $override = false): void {
        if (!$override and isset(self::$serializers[$type])) {
            throw new \InvalidArgumentException("Variable serializer ".$type." is already registered");
        }

        self::$serializers[$type] = $serializer;
    }

    public static function isSerializable(string $type): bool {
        return isset(self::$serializers[$type]);
    }

    public static function serialize(Variable $variable): ?array {
        $type = $variable::getTypeName();
        if (!isset(self::$serializers[$type])) return null;

        return [
            "type" => $type,
            "value" => (self::$serializers[$type])($variable),
        ];
    }

    public static function fallback(Variable $variable): array {
        return [
            "type" => StringVariable::getTypeName(),
            "value" => (string)$variable,
        ];
    }
}