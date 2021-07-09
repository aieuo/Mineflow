<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
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
                $variable = new StringVariable($level->getName());
                break;
            case "folderName":
                $variable = new StringVariable($level->getFolderName());
                break;
            case "id":
                $variable = new NumberVariable($level->getId());
                break;
            case "players":
                $variable = new ListVariable(array_map(function (Player $player) {
                    return new PlayerObjectVariable($player);
                    }, $level->getPlayers()));
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getWorld(): Level {
        /** @var Level $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "folderName" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }
}