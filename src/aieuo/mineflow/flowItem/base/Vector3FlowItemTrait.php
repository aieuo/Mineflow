<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\math\Vector3;

trait Vector3FlowItemTrait {

    /** @var string[] */
    private array $vector3VariableNames = [];

    public function getVector3VariableName(string $name = ""): string {
        return $this->vector3VariableNames[$name] ?? "";
    }

    public function setVector3VariableName(string $vector3, string $name = ""): void {
        $this->vector3VariableNames[$name] = $vector3;
    }

    public function getVector3(FlowItemExecutor $source, string $name = ""): Vector3 {
        $vector3 = $source->replaceVariables($rawName = $this->getVector3VariableName($name));
        $variable = $source->getVariable($vector3);

        if ($variable instanceof Vector3Variable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.vector3"], $rawName]));
    }

}
