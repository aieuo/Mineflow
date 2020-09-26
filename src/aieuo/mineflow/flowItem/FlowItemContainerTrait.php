<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\recipe\Recipe;

trait FlowItemContainerTrait {

    /** @var FlowItem[][] */
    private $items = [];

    /** @var mixed */
    private $lastResult;

    /**
     * @param FlowItem $action
     * @param string $name
     */
    public function addItem(FlowItem $action, string $name): void {
        $this->items[$name][] = $action;
    }

    /**
     * @param array $actions
     * @param string $name
     */
    public function setItems(array $actions, string $name): void {
        $this->items[$name] = $actions;
    }

    /**
     * @param int $index
     * @param string $name
     * @return FlowItem|null
     */
    public function getItem(int $index, string $name): ?FlowItem {
        return $this->items[$name][$index] ?? null;
    }

    /**
     * @param int $index
     * @param string $name
     */
    public function removeItem(int $index, string $name): void {
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

    public function executeAll(Recipe $recipe, string $name) {
        foreach ($this->getItems($name) as $i => $action) {
            $this->setLastResult(/** @noinspection PhpParamsInspection */ yield from $action->parent($this)->execute($recipe));
        }
        return true;
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function setLastResult($lastResult): void {
        $this->lastResult = $lastResult;
    }
}