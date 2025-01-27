<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\argument\ConditionArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\editor\ConditionArrayEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\utils\Language;

abstract class LogicalOperation extends SimpleCondition {

    /**
     * @param string $id
     * @param string $category
     * @param array<FlowItem&Condition> $conditions
     */
    public function __construct(
        string $id,
        string $category = FlowItemCategory::SCRIPT,
        array  $conditions = [],
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            ConditionArrayArgument::create("conditions", $conditions),
        ]);
    }

    public function getDetailKey(): string {
        return "condition.logical_operator.detail";
    }

    public function getDetailReplaces(): array {
        return [$this->getId(), ...parent::getDetailReplaces()];
    }

    public function getConditions(): ConditionArrayArgument {
        return $this->getArgument("conditions");
    }

    public function getEditors(): array {
        return [
            new ConditionArrayEditor($this->getConditions()),
        ];
    }

    public function loadSaveData(array $content): void {
        $this->getConditions()->load($content);
    }

    public function serializeContents(): array {
        return $this->getConditions()->jsonSerialize();
    }
}