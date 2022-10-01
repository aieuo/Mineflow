<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class Calculate2 extends FlowItem {

    protected string $name = "action.calculate2.name";
    protected string $detail = "action.calculate2.detail";
    protected array $detailDefaultReplace = ["value1", "value2", "operator", "result"];

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const CALC_MIN = 0;
    public const CALC_MAX = 1;
    public const CALC_POW = 2;
    public const CALC_LOG = 3;
    public const CALC_HYPOT = 4;
    public const CALC_ATAN2 = 5;
    public const CALC_ROUND = 6;

    private int $operator;

    private array $operatorSymbols = [
        "min(x, y)",
        "max(x, y)",
        "x^y",
        "log_y(x)",
        "√(x^2 + y^2)",
        "atan2(x, y)",
        "round(x, y)"
    ];

    public function __construct(
        private string $value1 = "",
        private string $value2 = "",
        string         $operator = null,
        private string $resultName = "result"
    ) {
        parent::__construct(self::CALCULATE2, FlowItemCategory::MATH);

        $this->operator = (int)($operator ?? self::CALC_MIN);
    }

    public function setValue1(string $value1): self {
        $this->value1 = $value1;
        return $this;
    }

    public function getValue1(): string {
        return $this->value1;
    }

    public function setValue2(string $value2): self {
        $this->value2 = $value2;
        return $this;
    }

    public function getValue2(): string {
        return $this->value2;
    }

    public function setOperator(int $operator): self {
        $this->operator = $operator;
        return $this;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getValue1() !== "" and $this->getValue2() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue1(), $this->getValue2(), $this->operatorSymbols[$this->getOperator()], $this->resultName]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value1 = $source->replaceVariables($this->getValue1());
        $value2 = $source->replaceVariables($this->getValue2());
        $resultName = $source->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $this->throwIfInvalidNumber($value1);
        $this->throwIfInvalidNumber($value2);

        $value1 = (float)$value1;
        $value2 = (float)$value2;
        $result = match ($operator) {
            self::CALC_MIN => min($value1, $value2),
            self::CALC_MAX => max($value1, $value2),
            self::CALC_POW => $value1 ** $value2,
            self::CALC_LOG => log($value1, $value2),
            self::CALC_HYPOT => hypot($value1, $value2),
            self::CALC_ATAN2 => atan2($value1, $value2),
            self::CALC_ROUND => round($value1, (int)$value2),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));
        yield true;
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@action.calculate2.form.value1", "10", $this->getValue1(), true),
            new ExampleNumberInput("@action.calculate2.form.value2", "20", $this->getValue2(), true),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue1($content[0]);
        $this->setValue2($content[1]);
        $this->setOperator($content[2]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getValue2(), $this->getOperator(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
