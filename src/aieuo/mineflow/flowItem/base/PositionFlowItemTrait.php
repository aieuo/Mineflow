<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;

trait PositionFlowItemTrait {

    /* @var string */
    private $positionVariableName = "pos";

    public function getPositionVariableName(): String {
        return $this->positionVariableName;
    }

    public function setPositionVariableName(string $name) {
        $this->positionVariableName = $name;
        return $this;
    }

    public function getPosition(Recipe $origin): ?Position {
        $name = $origin->replaceVariables($this->getPositionVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof PositionObjectVariable)) return null;
        return $variable->getPosition();
    }

    public function throwIfInvalidPosition(?Position $player) {
        if (!($player instanceof Position)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.position"]]));
        }
    }
}