<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\variable\DummyVariable;

interface FlowItemContainer {

    public const ACTION = "action";
    public const CONDITION = "condition";

    public function getContainerName(): string;

    public function addItem(FlowItem $action, string $name): void;

    public function setItems(array $actions, string $name): void;

    public function pushItem(int $index, FlowItem $action, string $name): void;

    public function getItem(int $index, string $name): ?FlowItem;

    public function removeItem(int $index, string $name): void;

    /**
     * @param string $name
     * @return FlowItem[]
     */
    public function getItems(string $name): array;

    /**
     * @return FlowItem[]
     */
    public function getActions(): array;

    /**
     * @return FlowItem[]
     */
    public function getConditions(): array;

    /**
     * @param FlowItem $flowItem
     * @param FlowItemContainer[] $containers
     * @param string $type
     * @return array<string, DummyVariable>
     */
    public function getAddingVariablesBefore(FlowItem $flowItem, array $containers, string $type): array;
}