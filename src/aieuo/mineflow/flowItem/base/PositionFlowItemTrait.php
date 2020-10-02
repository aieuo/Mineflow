<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;

trait PositionFlowItemTrait {

    /* @var string[] */
    private $positionVariableNames = [];

    public function getPositionVariableName(string $name = ""): string {
        return $this->positionVariableNames[$name] ?? "";
    }

    public function setPositionVariableName(string $position, string $name = "") {
        $this->positionVariableNames[$name] = $position;
        return $this;
    }

    public function getPosition(Recipe $origin, string $name = ""): ?Position {
        $position = $origin->replaceVariables($this->getPositionVariableName($name));

        $variable = $origin->getVariable($position);
        if (!($variable instanceof PositionObjectVariable)) return null;
        return $variable->getPosition();
    }

    public function throwIfInvalidPosition(?Position $position) {
        if (!($position instanceof Position)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.position"], $this->getPositionVariableName()]));
        }
    }
}