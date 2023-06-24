<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\item\Item;

#[Deprecated]
/**
 * @see ItemArgument
 */
interface ItemFlowItem {

    public function getItemVariableName(string $name = ""): string;

    public function setItemVariableName(string $item, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getItem(FlowItemExecutor $source, string $name = ""): Item;
}
