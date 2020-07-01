<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use pocketmine\block\Block;

trait BlockFlowItemTrait {

    /* @var string */
    private $blockVariableName = "block";

    public function getBlockVariableName(): string {
        return $this->blockVariableName;
    }

    public function setBlockVariableName(string $name) {
        $this->blockVariableName = $name;
        return $this;
    }

    public function getBlock(Recipe $origin): ?Block {
        $name = $origin->replaceVariables($this->getBlockVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof BlockObjectVariable)) return null;
        return $variable->getBlock();
    }

    public function throwIfInvalidBlock(?Block $block) {
        if (!($block instanceof Block)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.block"], $this->getBlockVariableName()]));
        }
    }
}