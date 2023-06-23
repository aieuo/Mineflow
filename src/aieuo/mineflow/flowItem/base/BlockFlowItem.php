<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\BlockPlaceholder;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\block\Block;

#[Deprecated]
/**
 * @see BlockPlaceholder
 */
interface BlockFlowItem {

    public function getBlockVariableName(string $name = ""): string;

    public function setBlockVariableName(string $block, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getBlock(FlowItemExecutor $source, string $name = ""): Block;
}
