<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;

abstract class OnlinePlayerCount extends FlowItem implements Condition {

    protected array $detailDefaultReplace = ["value"];

    private string $value;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $value = ""
    ) {
        parent::__construct($id, $category);

        $this->value = $value;
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

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getValue()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@condition.randomNumber.form.value", "5", $this->getValue(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue()];
    }
}
