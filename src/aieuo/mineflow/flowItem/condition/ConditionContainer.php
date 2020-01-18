<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemContainer;

interface ConditionContainer extends FlowItemContainer {

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition): void;

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions): void;

    /**
     * @param int $index
     * @return Condition|null
     */
    public function getCondition(int $index): ?Condition;

    /**
     * @param int $index
     */
    public function removeCondition(int $index): void;

    /**
     * @return Condition[]
     */
    public function getConditions(): array;
}