<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\Server;

class ServerObjectVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "server";
    }

    public function __construct(Server $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $server = $this->getServer();
        switch ($name) {
            case "name":
                return new StringVariable($server->getName());
            case "tick":
                return new NumberVariable($server->getTick());
            case "default_world":
                $world = $server->getWorldManager()->getDefaultWorld();
                if ($world === null) return null;
                return new WorldObjectVariable($world);
            case "worlds":
                return new ListVariable(array_map(fn(World $world) => new WorldObjectVariable($world), $server->getWorldManager()->getWorlds()));
            case "players":
                return new ListVariable(array_map(fn(Player $player) => new PlayerObjectVariable($player), array_values($server->getOnlinePlayers())));
            case "entities":
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if ($entity instanceof Player) {
                            $v = new PlayerObjectVariable($entity);
                        } elseif ($entity instanceof Human) {
                            $v = new HumanObjectVariable($entity);
                        } else {
                            $v = new EntityObjectVariable($entity);
                        }
                        $entities[] = $v;
                    }
                }
                return new ListVariable($entities);
            case "livings":
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if ($entity instanceof Player) {
                            $v = new PlayerObjectVariable($entity);
                        } elseif ($entity instanceof Human) {
                            $v = new HumanObjectVariable($entity);
                        } elseif ($entity instanceof Living) {
                            $v = new EntityObjectVariable($entity);
                        } else {
                            continue;
                        }
                        $entities[] = $v;
                    }
                }
                return new ListVariable($entities);
            case "ops":
                return new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true)));
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getServer(): Server {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return [
            "name" => new DummyVariable(StringVariable::class),
            "tick" => new DummyVariable(NumberVariable::class),
            "default_world" => new DummyVariable(WorldObjectVariable::class),
            "worlds" => new DummyVariable(ListVariable::class, WorldObjectVariable::getTypeName()),
            "players" => new DummyVariable(ListVariable::class, PlayerObjectVariable::getTypeName()),
            "entities" => new DummyVariable(ListVariable::class, EntityObjectVariable::getTypeName()),
            "livings" => new DummyVariable(ListVariable::class, EntityObjectVariable::getTypeName()),
            "ops" => new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
        ];
    }

    public function __toString(): string {
        return (string)(new MapVariable(self::getValuesDummy()));
    }
}