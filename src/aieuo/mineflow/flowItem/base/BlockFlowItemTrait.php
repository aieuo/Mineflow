<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use pocketmine\block\Block;

trait BlockFlowItemTrait {

    /* @var string[] */
    private $blockVariableNames = [];

    public function getBlockVariableName(string $name = ""): string {
        return $this->blockVariableNames[$name] ?? "";
    }

    public function setBlockVariableName(string $block, string $name = "") {
        $this->blockVariableNames[$name] = $block;
        return $this;
    }

    public function getBlock(Recipe $origin, string $name = ""): ?Block {
        $block = $origin->replaceVariables($this->getBlockVariableName($name));

        $variable = $origin->getVariable($block);
        if (!($variable instanceof BlockObjectVariable)) return null;
        return $variable->getBlock();
    }

    public function throwIfInvalidBlock(?Block $block) {
        if (!($block instanceof Block)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.block"], $this->getBlockVariableName()]));
        }
    }
}