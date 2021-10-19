<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class FourArithmeticOperations extends FlowItem {

    protected string $id = self::FOUR_ARITHMETIC_OPERATIONS;

    protected string $name = "action.fourArithmeticOperations.name";
    protected string $detail = "action.fourArithmeticOperations.detail";
    protected array $detailDefaultReplace = ["value1", "value2", "operator", "result"];

    protected string $category = Category::MATH;
    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const ADDITION = 0;
    public const SUBTRACTION = 1;
    public const MULTIPLICATION = 2;
    public const DIVISION = 3;
    public const MODULO = 4;

    private string $value1;
    private int $operator;
    private string $value2;
    private string $resultName;

    private array $operatorSymbols = ["+", "-", "*", "/", "％"];

    public function __construct(string $value1 = "", int $operator = self::ADDITION, string $value2 = "", string $resultName = "result") {
        $this->value1 = $value1;
        $this->operator = $operator;
        $this->value2 = $value2;
        $this->resultName = $resultName;
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
        return Language::get($this->detail, [$this->getValue1(), $this->operatorSymbols[$this->getOperator()] ?? "?", $this->getValue2(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value1 = $source->replaceVariables($this->getValue1());
        $value2 = $source->replaceVariables($this->getValue2());
        $resultName = $source->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $this->throwIfInvalidNumber($value1);
        $this->throwIfInvalidNumber($value2);

        $result = match ($operator) {
            self::ADDITION => (float)$value1 + (float)$value2,
            self::SUBTRACTION => (float)$value1 - (float)$value2,
            self::MULTIPLICATION => (float)$value1 * (float)$value2,
            self::DIVISION => (float)$value1 / $this->getFloat($value2, exclude: [0.0]),
            self::MODULO => (float)$value1 % $this->getFloat($value2, exclude: [0.0]),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));
        yield FlowItemExecutor::CONTINUE;
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@action.fourArithmeticOperations.form.value1", "10", $this->getValue1(), true),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleNumberInput("@action.fourArithmeticOperations.form.value2", "50", $this->getValue2(), true),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}