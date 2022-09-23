<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;

trait AxisAlignedBBFlowItemTrait {

    /** @var string[] */
    private array $aabbVariableNames = [];

    public function getAxisAlignedBBVariableName(string $name = ""): string {
        return $this->aabbVariableNames[$name] ?? "";
    }

    public function setAxisAlignedBBVariableName(string $aabb, string $name = ""): void {
        $this->aabbVariableNames[$name] = $aabb;
    }

    public function getAxisAlignedBB(FlowItemExecutor $source, string $name = ""): AxisAlignedBB {
        $aabb = $source->replaceVariables($rawName = $this->getAxisAlignedBBVariableName($name));
        $variable = $source->getVariable($aabb);

        if ($variable instanceof AxisAlignedBBVariable and ($aabb = $variable->getAxisAlignedBB()) instanceof AxisAlignedBB) {
            return $aabb;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.aabb"], $rawName]));
    }

}