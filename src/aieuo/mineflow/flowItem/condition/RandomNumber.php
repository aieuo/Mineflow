<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class RandomNumber extends FlowItem implements Condition {

    protected string $id = self::RANDOM_NUMBER;

    protected string $name = "condition.randomNumber.name";
    protected string $detail = "condition.randomNumber.detail";
    protected array $detailDefaultReplace = ["min", "max", "value"];

    protected string $category = Category::MATH;

    private string $min;
    private string $max;
    private string $value;

    public function __construct(string $min = "", string $max = "", string $value = "") {
        $this->min = $min;
        $this->max = $max;
        $this->value = $value;
    }

    public function setMin(string $min): void {
        $this->min = $min;
    }

    public function getMin(): string {
        return $this->min;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->min !== "" and $this->max !== "" and $this->value !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMin(), $this->getMax(), $this->getValue()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $min = $source->replaceVariables($this->getMin());
        $max = $source->replaceVariables($this->getMax());
        $value = $source->replaceVariables($this->getValue());

        $this->throwIfInvalidNumber($min);
        $this->throwIfInvalidNumber($max);
        $this->throwIfInvalidNumber($value);

        FlowItemExexutor::CONTINUE;
        return mt_rand(min((int)$min, (int)$max), max((int)$min, (int)$max)) === (int)$value;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@condition.randomNumber.form.min", "0", $this->getMin(), true),
            new ExampleNumberInput("@condition.randomNumber.form.max", "10", $this->getMax(), true),
            new ExampleNumberInput("@condition.randomNumber.form.value", "0", $this->getValue(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setMin($content[0]);
        $this->setMax($content[1]);
        $this->setValue($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMin(), $this->getMax(), $this->getValue()];
    }
}