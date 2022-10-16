<?php

declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\Vector3ObjectVariable;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_map;

class VariableDeserializer {

    /** @var array<string, callable(mixed): ?Variable> */
    private static array $deserializers = [];

    public static function init(): void {
        self::register(Variable::STRING, static fn($data) => new StringVariable((string)$data));
        self::register(Variable::NUMBER, static fn($data) => new NumberVariable((float)$data));
        self::register(Variable::BOOLEAN, static fn($data) => new BoolVariable((bool)$data));
        self::register(Variable::NULL, static fn() => new NullVariable());
        self::register(Variable::LIST, static function ($data) {
            return new ListVariable(array_map(fn($v) => self::deserialize($v) ?? new NullVariable(), $data));
        });
        self::register(Variable::MAP, static function ($data) {
            return new MapVariable(array_map(fn($v) => self::deserialize($v) ?? new NullVariable(), $data));
        });

        self::register("item", static fn($data) => new ItemObjectVariable(Item::jsonDeserialize($data)));
        self::register("vector3", static function ($data) {
            return new Vector3ObjectVariable(new Vector3($data["x"], $data["y"], $data["z"]));
        });
        self::register("position", static function ($data) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
            return new Vector3ObjectVariable(new Position($data["x"], $data["y"], $data["z"], $world));
        });
        self::register("location", static function ($data) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
            return new Vector3ObjectVariable(new Location($data["x"], $data["y"], $data["z"], $world, $data["yaw"], $data["pitch"]));
        });
    }

    /**
     * @param string|int $type
     * @param callable(mixed): ?Variable $deserializer
     * @param bool $override
     * @return void
     */
    public static function register(string|int $type, callable $deserializer, bool $override = false): void {
        if (!$override and isset(self::$deserializers[$type])) {
            throw new \InvalidArgumentException("Variable deserializer ".$type." is already registered");
        }

        self::$deserializers[$type] = $deserializer;
    }

    public static function deserialize(array $data): ?Variable {
        if (!isset($data["value"]) or !isset($data["type"])) return null;
        if (!isset(self::$deserializers[$data["type"]])) return null;

        return (self::$deserializers[$data["type"]])($data["value"]);
    }

}
