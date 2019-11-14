<?php

namespace aieuo\mineflow\action;

interface ActionContainer {

    public function addAction(Action $action): void;

    public function getAction(int $index): ?Action;

    public function removeAction(int $index): void;

    /**
     * @return Action[]
     */
    public function getActions(): array;
}