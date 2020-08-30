<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

trait ActionContainerTrait {

    /** @var Action[] */
    private $actions = [];

    /** @var bool */
    private $lastResult;

    /**
     * @param Action $action
     */
    public function addAction(Action $action): void {
        $this->actions[] = $action;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions): void {
        $this->actions = $actions;
    }

    /**
     * @param int $index
     * @return Action|null
     */
    public function getAction(int $index): ?Action {
        return $this->actions[$index] ?? null;
    }

    /**
     * @param int $index
     */
    public function removeAction(int $index): void {
        unset($this->actions[$index]);
        $this->actions = array_merge($this->actions);
    }

    /**
     * @return Action[]
     */
    public function getActions(): array {
        return $this->actions;
    }

    public function executeActions(Recipe $recipe) {
        foreach ($this->getActions() as $i => $action) {
            $this->lastResult = /** @noinspection PhpParamsInspection */
                yield from $action->parent($this)->execute($recipe);
        }
        return true;
    }

    public function getLastActionResult(): ?bool {
        return $this->lastResult;
    }
}