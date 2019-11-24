<?php

namespace aieuo\mineflow\condition;

interface ConditionContainer {

    public function addCondition(Conditionable $condition): void;

    public function setConditions(array $conditions): void;

    public function getCondition(int $index): ?Conditionable;

    public function removeCondition(int $index): void;

    /**
     * @return Conditionable[]
     */
    public function getConditions(): array;
}