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
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class ServerObjectVariable extends ObjectVariable {

    public function __construct(Server $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $server = $this->getServer();
        switch ($index) {
            case "name":
                $variable = new StringVariable($server->getName());
                break;
            case "tick":
                $variable = new NumberVariable($server->getTick());
                break;
            case "default_world":
                $world = $server->getDefaultLevel();
                if ($world === null) return null;
                $variable = new WorldObjectVariable($world);
                break;
            case "worlds":
                $variable = new ListVariable(array_map(function (Level $world) { return new WorldObjectVariable($world); }, $server->getLevels()));
                break;
            case "players":
                $variable = new ListVariable(array_map(function (Player $player) { return new PlayerObjectVariable($player); }, array_values($server->getOnlinePlayers())));
                break;
            case "entities":
                $entities = [];
                foreach ($server->getLevels() as $level) {
                    foreach ($level->getEntities() as $entity) {
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
                $variable = new ListVariable($entities);
                break;
            case "livings":
                $entities = [];
                foreach ($server->getLevels() as $level) {
                    foreach ($level->getEntities() as $entity) {
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
                $variable = new ListVariable($entities);
                break;
            case "ops":
                $variable = new ListVariable(array_map(function (string $name) { return new StringVariable($name); }, $server->getOps()->getAll(true)));
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getServer(): Server {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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