<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;

class ComparisonNumber extends FlowItem implements Condition {

    protected string $name = "condition.comparisonNumber.name";
    protected string $detail = "condition.comparisonNumber.detail";
    protected array $detailDefaultReplace = ["value1", "operator", "value2"];

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

    public function setValues(string $value1, string $value2): self {
        $this->value1 = $value1;
        $this->value2 = $value2;
        return $this;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue1(), $this->operatorSymbols[$this->getOperator()], $this->getValue2()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value1 = $source->replaceVariables($this->getValue1());
        $value2 = $source->replaceVariables($this->getValue2());
        $operator = $this->getOperator();

        $this->throwIfInvalidNumber($value1);
        $this->throwIfInvalidNumber($value2);

        $value1 = (float)$value1;
        $value2 = (float)$value2;
        $result = match ($operator) {
            self::EQUAL => $value1 === $value2,
            self::NOT_EQUAL => $value1 !== $value2,
            self::GREATER => $value1 > $value2,
            self::LESS => $value1 < $value2,
            self::GREATER_EQUAL => $value1 >= $value2,
            self::LESS_EQUAL => $value1 <= $value2,
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };
        yield true;
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@condition.comparisonNumber.form.value1", "10", $this->getValue1(), true),
            new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleNumberInput("@condition.comparisonNumber.form.value2", "50", $this->getValue2(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2()];
    }
}
