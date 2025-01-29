<?php

declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_map;
use function base64_decode;
use function is_array;

class VariableDeserializer {

    /** @var array<int|string, callable(mixed): ?Variable> */
    private static array $deserializers = [];

    public static function init(): void {
        self::register(StringVariable::getTypeName(), static fn($data) => new StringVariable((string)$data), aliases: [0]);
        self::register(NumberVariable::getTypeName(), static fn($data) => new NumberVariable((float)$data), aliases: [1]);
        self::register(BooleanVariable::getTypeName(), static fn($data) => new BooleanVariable((bool)$data), aliases: [5]);
        self::register(NullVariable::getTypeName(), static fn() => new NullVariable(), aliases: [6]);
        self::register(ListVariable::getTypeName(), static function ($data) {
            return new ListVariable(array_map(fn($v) => self::deserialize($v) ?? new NullVariable(), $data));
        }, aliases: [2]);
        self::register(MapVariable::getTypeName(), static function ($data) {
            return new MapVariable(array_map(fn($v) => self::deserialize($v) ?? new NullVariable(), $data));
        }, aliases: [3]);

        self::register(ItemVariable::getTypeName(), static function($data) {
            if (is_array($data)) {
                return new ItemVariable(Item::legacyJsonDeserialize($data));
            }

            $tag = (new BigEndianNbtSerializer())->read(base64_decode($data))->mustGetCompoundTag();
            return new ItemVariable(Item::nbtDeserialize($tag));
        });
        self::register(Vector3Variable::getTypeName(), static function ($data) {
            return new Vector3Variable(new Vector3($data["x"], $data["y"], $data["z"]));
        });
        self::register(PositionVariable::getTypeName(), static function ($data) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
            return new PositionVariable(new Position($data["x"], $data["y"], $data["z"], $world));
        });
        self::register(LocationVariable::getTypeName(), static function ($data) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
            return new LocationVariable(new Location($data["x"], $data["y"], $data["z"], $world, $data["yaw"], $data["pitch"]));
        });
    }

    /**
     * @param string $type
     * @param callable(mixed): ?Variable $deserializer
     * @param bool $override
     * @param (int|string)[] $aliases
     * @return void
     */
    public static function register(string $type, callable $deserializer, bool $override = false, array $aliases = []): void {
        if (!$override and isset(self::$deserializers[$type])) {
            throw new \InvalidArgumentException("Variable deserializer ".$type." is already registered");
        }

        self::$deserializers[$type] = $deserializer;
        foreach ($aliases as $alias) {
            self::$deserializers[$alias] = $deserializer;
        }
    }

    public static function isDeserializable(string $type): bool {
        return isset(self::$deserializers[$type]);
    }

    public static function deserialize(array $data): ?Variable {
        if (!isset($data["value"]) or !isset($data["type"])) return null;
        if (!isset(self::$deserializers[$data["type"]])) return null;

        return (self::$deserializers[$data["type"]])($data["value"]);
    }

}