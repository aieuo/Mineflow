<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;

abstract class OnlinePlayerCount extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER,
        private string $value = ""
    ) {
        parent::__construct($id, $category);
    }

    public function getDetailDefaultReplaces(): array {
        return ["value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getValue()];
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->value !== "";
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleNumberInput("@condition.randomNumber.form.value", "5", $this->getValue(), true),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue()];
    }
}
