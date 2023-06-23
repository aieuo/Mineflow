<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\BlockVariable;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\block\Block;

#[Deprecated]
/**
 * @see BlockPlaceholder
 */
trait BlockFlowItemTrait {

    /* @var string[] */
    private array $blockVariableNames = [];

    public function getBlockVariableName(string $name = ""): string {
        return $this->blockVariableNames[$name] ?? "";
    }

    public function setBlockVariableName(string $block, string $name = ""): void {
        $this->blockVariableNames[$name] = $block;
    }

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return Block
     * @throws InvalidFlowValueException
     */
    public function getBlock(FlowItemExecutor $source, string $name = ""): Block {
        $block = $source->replaceVariables($rawName = $this->getBlockVariableName($name));

        $variable = $source->getVariable($block);
        if ($variable instanceof BlockVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.block"], $rawName]));
    }
}
