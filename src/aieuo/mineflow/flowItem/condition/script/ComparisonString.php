<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use function str_ends_with;

class ComparisonString extends FlowItem implements Condition {

    protected string $name = "condition.comparisonString.name";
    protected string $detail = "condition.comparisonString.detail";
    protected array $detailDefaultReplace = ["value1", "operator", "value2"];

    public const EQUALS = 0;
    public const NOT_EQUALS = 1;
    public const CONTAINS = 2;
    public const NOT_CONTAINS = 3;
    public const STARTS_WITH = 4;
    public const ENDS_WITH = 5;

    private string $value1;
    private int $operator;
    private string $value2;

    private array $operatorSymbols = ["==", "!=", "contains", "not contains", "starts with", "ends with"];

    public function __construct(string $value1 = "", string $operator = null, string $value2 = "") {
        parent::__construct(self::COMPARISON_STRING, FlowItemCategory::SCRIPT);

        $this->value1 = $value1;
        $this->operator = (int)($operator ?? self::EQUALS);
        $this->value2 = $value2;
    }

    public function setValues(string $value1, string $value2): self {
        $this->value1 = $value1;
        $this->value2 = $value2;
        return $this;
    }

    public function getValue1(): string {
        return $this->value1;
    }

    public function getValue2(): string {
        return $this->value2;
    }

    public function setOperator(int $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function isDataValid(): bool {
        return $this->value1 !== "";
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

        $result = match ($operator) {
            self::EQUALS => $value1 === $value2,
            self::NOT_EQUALS => $value1 !== $value2,
            self::CONTAINS => str_contains($value1, $value2),
            self::NOT_CONTAINS => !str_contains($value1, $value2),
            self::STARTS_WITH => str_starts_with($value1, $value2),
            self::ENDS_WITH => str_ends_with($value1, $value2),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };
        yield true;
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@condition.comparisonNumber.form.value1", "10", $this->getValue1(), true),
            new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleInput("@condition.comparisonNumber.form.value2", "50", $this->getValue2(), false),
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
