<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\world\Position;

trait PositionFlowItemTrait {

    /* @var string[] */
    private array $positionVariableNames = [];

    public function getPositionVariableName(string $name = ""): string {
        return $this->positionVariableNames[$name] ?? "";
    }

    public function setPositionVariableName(string $position, string $name = ""): void {
        $this->positionVariableNames[$name] = $position;
    }

    public function getPosition(FlowItemExecutor $source, string $name = ""): Position {
        $position = $source->replaceVariables($rawName = $this->getPositionVariableName($name));

        $variable = $source->getVariable($position);
        if ($variable instanceof PositionObjectVariable and ($position = $variable->getPosition()) instanceof Position) {
            return $position;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.position"], $rawName]));
    }
}