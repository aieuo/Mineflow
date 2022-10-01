<?php

namespace aieuo\mineflow\flowItem;

use function array_merge;
use function array_splice;

trait FlowItemContainerTrait {

    /** @var FlowItem[][] */
    private array $items = [];

    public function addItem(FlowItem $action, string $name): void {
        $this->items[$name][] = $action;
    }

    /**
     * @param FlowItem[] $actions
     * @param string $name
     */
    public function setItems(array $actions, string $name): void {
        $this->items[$name] = $actions;
    }

    public function pushItem(int $index, FlowItem $action, string $name): void {
        if (!isset($this->items[$name])) $this->items[$name] = [];

        array_splice($this->items[$name], $index, 0, [$action]);
    }

    public function getItem(int $index, string $name): ?FlowItem {
        return $this->items[$name][$index] ?? null;
    }

    public function removeItem(int $index, string $name): void {
        if (!isset($this->items[$name])) return;

        unset($this->items[$name][$index]);
        $this->items[$name] = array_merge($this->items[$name]);
    }

    /**
     * @param string $name
     * @return FlowItem[]
     */
    public function getItems(string $name): array {
        return $this->items[$name] ?? [];
    }

    public function addAction(FlowItem $action): void {
        $this->addItem($action, FlowItemContainer::ACTION);
    }

    /**
     * @param FlowItem[] $actions
     */
    public function setActions(array $actions): void {
        $this->setItems($actions, FlowItemContainer::ACTION);
    }

    public function pushAction(int $index, FlowItem $action): void {
        $this->pushItem($index, $action, FlowItemContainer::ACTION);
    }

    public function getAction(int $index): ?FlowItem {
        return $this->getItem($index, FlowItemContainer::ACTION);
    }

    public function removeAction(int $index): void {
        $this->removeItem($index, FlowItemContainer::ACTION);
    }

    /**
     * @return FlowItem[]
     */
    public function getActions(): array {
        return $this->getItems(self::ACTION);
    }

    public function addCondition(FlowItem $condition): void {
        $this->addItem($condition, FlowItemContainer::CONDITION);
    }

    /**
     * @param FlowItem[] $conditions
     */
    public function setConditions(array $conditions): void {
        $this->setItems($conditions, FlowItemContainer::CONDITION);
    }

    public function pushCondition(int $index, FlowItem $condition): void {
        $this->pushItem($index, $condition, FlowItemContainer::CONDITION);
    }

    public function getCondition(int $index): ?FlowItem {
        return $this->getItem($index, FlowItemContainer::CONDITION);
    }

    public function removeCondition(int $index): void {
        $this->removeItem($index, FlowItemContainer::CONDITION);
    }

    /**
     * @return FlowItem[]
     */
    public function getConditions(): array {
        return $this->getItems(self::CONDITION);
    }

    public function getAddingVariablesBefore(FlowItem $flowItem, array $containers, string $type): array {
        $variables = [];

        /** @var FlowItem|FlowItemContainer $target */
        $target = array_shift($containers);
        if ($target !== null) {
            $variables = array_merge($target->getAddingVariables(), $target->getAddingVariablesBefore($flowItem, $containers, $type));
        }
        $target = $target ?? $flowItem;

        $variablesMerge = [];
        foreach ($this->getItems($type) as $item) {
            if ($item === $target) break;
            $variablesMerge[] = $item->getAddingVariables();
        }
        return array_merge($variables, ...$variablesMerge);
    }
}