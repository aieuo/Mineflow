<?php

namespace aieuo\mineflow\trigger\block;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\block\Block;

class BlockTrigger extends Trigger {

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Trigger::TYPE_BLOCK, $key, $subKey);
    }

    /**
     * @param Block $block
     * @return array
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($block): array {
        return DefaultVariables::getBlockVariables($block);
    }

    public function getVariablesDummy(): array {
        return [new DummyVariable("block", DummyVariable::BLOCK)];
    }
}