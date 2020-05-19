<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;

trait ActionContainerTrait {

    /** @var Action[] */
    private $actions = [];

    /** @var bool */
    private $wait = false;
    /** @var array|null */
    private $last = null;
    /** @var bool */
    private $exit = false;
    /** @var bool */
    private $break = false;

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

    public function executeActions(Recipe $recipe, ?ActionContainer $parent = null, int $start = 0): bool {
        $actions = $this->getActions();
        $count = count($actions);
        for ($i=$start; $i<$count; $i++) {
            if ($this->exit) {
                if ($parent instanceof ActionContainer) $parent->exitRecipe();
                break;
            }

            $action = $actions[$i];
            try {
                /** @var ActionContainer $this */
                $this->lastResult = $action->parent($this)->execute($recipe);
            } catch (\UnexpectedValueException $e) {
                if (!empty($e->getMessage())) Logger::warning($e->getMessage(), $recipe->getTarget());
                Logger::warning(Language::get("recipe.execute.failed", [$recipe->getPathname(), $i, $action->getName()]), $recipe->getTarget());
                return false;
            }

            if ($this->wait) {
                $this->last = [$recipe, $parent, $i + 1];
                if ($parent instanceof ActionContainer) $parent->wait();
                return true;
            }
        }
        return true;
    }

    public function wait() {
        $this->wait = true;
    }

    public function isWaiting(): bool {
        return $this->wait;
    }

    public function resume() {
        $last = $this->last;
        if ($last === null) return;

        $this->wait = false;
        $this->last = null;

        $this->executeActions(...$last);

        if ($last[1] instanceof ActionContainer) $last[1]->resume();
    }

    public function exitRecipe() {
        $this->exit = true;
    }

    public function break() {
        $this->break = true;
    }

    public function getLastActionResult(): ?bool {
        return $this->lastResult;
    }
}