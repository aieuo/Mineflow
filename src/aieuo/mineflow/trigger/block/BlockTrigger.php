<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\block;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\block\Block;

class BlockTrigger extends Trigger {

    public function __construct(private readonly string $position) {
        parent::__construct(Triggers::BLOCK);
    }

    public function getPositionString(): string {
        return $this->position;
    }

    /**
     * @param Block $block
     * @return array
     */
    public function getVariables(mixed $block): array {
        return DefaultVariables::getBlockVariables($block);
    }

    public function getVariablesDummy(): array {
        return [
            "block" => new DummyVariable(BlockVariable::class)
        ];
    }

    public function hash(): string|int {
        return $this->position;
    }

    public function __toString(): string {
        return Language::get("trigger.block.string", [$this->getPositionString()]);
    }
}