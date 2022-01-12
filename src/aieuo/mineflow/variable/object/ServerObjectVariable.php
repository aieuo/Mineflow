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

    public function __construct(Server $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $server = $this->getServer();
        switch ($index) {
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
                return parent::getValueFromIndex($index);
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getServer(): Server {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "tick" => new DummyVariable(DummyVariable::NUMBER),
            "default_world" => new DummyVariable(DummyVariable::WORLD),
            "worlds" => new DummyVariable(DummyVariable::LIST, DummyVariable::WORLD),
            "players" => new DummyVariable(DummyVariable::LIST, DummyVariable::PLAYER),
            "entities" => new DummyVariable(DummyVariable::LIST, DummyVariable::ENTITY),
            "livings" => new DummyVariable(DummyVariable::LIST, DummyVariable::ENTITY),
            "ops" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
        ]);
    }

    public function __toString(): string {
        return (string)(new MapVariable(self::getValuesDummy()));
    }
}