<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

interface FlowItemContainer {

    public const ACTION = "action";
    public const CONDITION = "condition";

    public function getContainerItemType(): string;

    public function addItem(FlowItem $action): void;

    public function setItems(array $actions): void;

    public function pushItem(int $index, FlowItem $action): void;

    public function getItem(int $index): ?FlowItem;

    public function removeItem(int $index): void;

    /**
     * @return FlowItem[]
     */
    public function getItems(): array;

    /**
     * @return FlowItem[]
     */
    public function getItemsFlatten(): array;
}