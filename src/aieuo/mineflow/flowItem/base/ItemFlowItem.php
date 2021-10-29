<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\item\Item;

interface ItemFlowItem {

    public function getItemVariableName(string $name = ""): string;

    public function setItemVariableName(string $item, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getItem(FlowItemExecutor $source, string $name = ""): Item;
}