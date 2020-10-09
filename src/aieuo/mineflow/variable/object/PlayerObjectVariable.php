<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\Player;

class PlayerObjectVariable extends HumanObjectVariable {

    public function __construct(Player $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? $value->getName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $player = $this->getPlayer();
        switch ($index) {
            case "name":
                $variable = new StringVariable($player->getName(), "name");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getPlayer(): Player {
        /** @var Player $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(string $name): array {
        return array_merge(parent::getValuesDummy($name), [
            new DummyVariable($name.".name", DummyVariable::STRING),
        ]);
    }
}