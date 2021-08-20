<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\level\Level;
use pocketmine\Player;

class WorldObjectVariable extends ObjectVariable {

    public function __construct(Level $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getFolderName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $level = $this->getWorld();
        switch ($index) {
            case "name":
                return new StringVariable($level->getName());
            case "folderName":
                return new StringVariable($level->getFolderName());
            case "id":
                return new NumberVariable($level->getId());
            case "players":
                return new ListVariable(array_values(array_map(fn(Player $player) => new PlayerObjectVariable($player), $level->getPlayers())));
            case "entities":
                $entities = [];
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
                return new ListVariable($entities);
            case "livings":
                $entities = [];
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
                return new ListVariable($entities);
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getWorld(): Level {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "folderName" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }
}