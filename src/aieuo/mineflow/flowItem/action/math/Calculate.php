<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use function abs;

class Calculate extends FlowItem {

    protected string $id = self::CALCULATE;

    protected string $name = "action.calculate.name";
    protected string $detail = "action.calculate.detail";
    protected array $detailDefaultReplace = ["value", "operator", "result"];

    protected string $category = Category::MATH;
    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const SQUARE = 0;
    public const SQUARE_ROOT = 1;
    public const FACTORIAL = 2;
    public const CALC_ABS = 3;
    public const CALC_LOG = 4;
    public const CALC_SIN = 5;
    public const CALC_COS = 6;
    public const CALC_TAN = 7;
    public const CALC_ASIN = 8;
    public const CALC_ACOS = 9;
    public const CALC_ATAN = 10;
    public const CALC_DEG2RAD = 11;
    public const CALC_RAD2DEG = 12;
    public const CALC_FLOOR = 13;
    public const CALC_ROUND = 14;
    public const CALC_CEIL = 15;

    private string $value;
    private int $operator;
    private string $resultName;

    private array $operatorSymbols = ["x^2", "√x", "x!", "abs(x)", "log(x)", "sin(x)", "cos(x)", "tan(x)", "asin(x)", "acos(x)", "atan(x)", "deg2rad(x)", "rad2deg(x)", "floor(x)", "round(x)", "ceil(x)"];

    public function __construct(string $value = "", string $operator = null, string $resultName = "result") {
        $this->value = $value;
        $this->operator = (int)($operator ?? self::SQUARE);
        $this->resultName = $resultName;
    }

    public function setValue(string $value): self {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string {
        return $this->value;
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
        return $this->getValue() !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getValue(), $this->operatorSymbols[$this->getOperator()], $this->resultName]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $this->getFloat($source->replaceVariables($this->getValue()));
        $resultName = $source->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $result = match ($operator) {
            self::SQUARE => $value * $value,
            self::SQUARE_ROOT => sqrt($value),
            self::FACTORIAL => $this->factorial((int)$value),
            self::CALC_ABS => abs($value),
            self::CALC_LOG => log10($value),
            self::CALC_SIN => sin($value),
            self::CALC_COS => cos($value),
            self::CALC_TAN => tan($value),
            self::CALC_ASIN => asin($value),
            self::CALC_ACOS => acos($value),
            self::CALC_ATAN => atan($value),
            self::CALC_DEG2RAD => deg2rad($value),
            self::CALC_RAD2DEG => rad2deg($value),
            self::CALC_FLOOR => floor($value),
            self::CALC_ROUND => round($value),
            self::CALC_CEIL => ceil($value),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));
        yield FlowItemExecutor::CONTINUE;
        return $result;
    }

    private function factorial(int $value): int {
        $result = 1;
        for ($i = abs($value); $i > 1; $i--) {
            $result *= $i;
        }
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.calculate.form.value", "10", $this->getValue(), true),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        $this->setOperator($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getOperator(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}