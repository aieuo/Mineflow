<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class ComparisonNumber extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public const EQUAL = 0;
    public const NOT_EQUAL = 1;
    public const GREATER = 2;
    public const LESS = 3;
    public const GREATER_EQUAL = 4;
    public const LESS_EQUAL = 5;

    private array $operatorSymbols = ["==", "!=", ">", "<", ">=", "<="];

    public function __construct(
        private string $value1 = "",
        private int    $operator = self::EQUAL,
        private string $value2 = ""
    ) {
        parent::__construct(self::COMPARISON_NUMBER, FlowItemCategory::SCRIPT);
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "operator", "value2"];
    }

    public function getDetailReplaces(): array {
        return [$this->getValue1(), $this->operatorSymbols[$this->getOperator()], $this->getValue2()];
    }

    public function setValues(string $value1, string $value2): void {
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    public function getValue1(): ?string {
        return $this->value1;
    }

    public function getValue2(): ?string {
        return $this->value2;
    }

    public function setOperator(int $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function isDataValid(): bool {
        return $this->value1 !== "" and $this->value2 !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->getFloat($source->replaceVariables($this->getValue1()));
        $value2 = $this->getFloat($source->replaceVariables($this->getValue2()));
        $operator = $this->getOperator();

        $result = match ($operator) {
            self::EQUAL => $value1 === $value2,
            self::NOT_EQUAL => $value1 !== $value2,
            self::GREATER => $value1 > $value2,
            self::LESS => $value1 < $value2,
            self::GREATER_EQUAL => $value1 >= $value2,
            self::LESS_EQUAL => $value1 <= $value2,
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        yield Await::ALL;
        return $result;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleNumberInput("@condition.comparisonNumber.form.value1", "10", $this->getValue1(), true),
            new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleNumberInput("@condition.comparisonNumber.form.value2", "50", $this->getValue2(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2()];
    }
}
