<?php

namespace aieuo\mineflow\flowItem\condition;

trait ConditionContainerTrait {

    /** @var Condition[] */
    private $conditions = [];

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition): void {
        $this->conditions[] = $condition;
    }

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions): void {
        $this->conditions = $conditions;
    }

    /**
     * @param int $index
     * @return Condition|null
     */
    public function getCondition(int $index): ?Condition {
        return $this->conditions[$index] ?? null;
    }

    /**
     * @param int $index
     */
    public function removeCondition(int $index): void {
        unset($this->conditions[$index]);
        $this->conditions = array_merge($this->conditions);
    }

    /**
     * @return Condition[]
     */
    public function getConditions(): array {
        return $this->conditions;
    }
}