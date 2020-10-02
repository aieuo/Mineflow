<?php

namespace aieuo\mineflow\flowItem;

interface FlowItemContainer {

    const ACTION = "action";
    const CONDITION = "condition";

    /**
     * @return string
     */
    public function getContainerName(): string;

    /**
     * @param FlowItem $action
     * @param string $name
     */
    public function addItem(FlowItem $action, string $name): void;

    /**
     * @param array $actions
     * @param string $name
     */
    public function setItems(array $actions, string $name): void;

    /**
     * @param int $index
     * @param FlowItem $action
     * @param string $name
     */
    public function pushItem(int $index, FlowItem $action, string $name): void;

    /**
     * @param int $index
     * @param string $name
     * @return FlowItem|null
     */
    public function getItem(int $index, string $name): ?FlowItem;

    /**
     * @param int $index
     * @param string $name
     */
    public function removeItem(int $index, string $name): void;

    /**
     * @param string $name
     * @return FlowItem[]
     */
    public function getItems(string $name): array;

    /**
     * @return mixed
     */
    public function getLastResult();

    /**
     * @param FlowItem $flowItem
     * @param array $containers
     * @param string $type
     * @return mixed
     */
    public function getAddingVariablesBefore(FlowItem $flowItem, array $containers, string $type);
}