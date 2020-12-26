<?php

namespace aieuo\mineflow\trigger\block;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\block\Block;

class BlockTrigger extends Trigger {

    /**
     * @param string $key
     * @param string $subKey
     * @return self
     */
    public static function create(string $key, string $subKey = ""): Trigger {
        return new BlockTrigger($key, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Triggers::BLOCK, $key, $subKey);
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

    public function __toString(): string {
        return Language::get("trigger.block.string", [$this->getKey()]);
    }
}