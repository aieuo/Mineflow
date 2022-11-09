<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\math;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class RandomNumber extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct(
        private string $min = "",
        private string $max = "",
        private string $value = ""
    ) {
        parent::__construct(self::RANDOM_NUMBER, FlowItemCategory::MATH);
    }

    public function getDetailDefaultReplaces(): array {
        return ["min", "max", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getMin(), $this->getMax(), $this->getValue()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->getInt($source->replaceVariables($this->getMin()));
        $max = $this->getInt($source->replaceVariables($this->getMax()));
        $value = $this->getInt($source->replaceVariables($this->getValue()));

        yield Await::ALL;
        return mt_rand(min($min, $max), max($min, $max)) === $value;
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
