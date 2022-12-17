<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class FourArithmeticOperations extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const ADDITION = 0;
    public const SUBTRACTION = 1;
    public const MULTIPLICATION = 2;
    public const DIVISION = 3;
    public const MODULO = 4;

    private array $operatorSymbols = ["+", "-", "*", "/", "％"];

    public function __construct(
        private string $value1 = "",
        private int $operator = self::ADDITION,
        private string $value2 = "",
        private string $resultName = "result"
    ) {
        parent::__construct(self::FOUR_ARITHMETIC_OPERATIONS, FlowItemCategory::MATH);
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "value2", "operator", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getValue1(), $this->operatorSymbols[$this->getOperator()] ?? "?", $this->getValue2(), $this->getResultName()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->getFloat($source->replaceVariables($this->getValue1()));
        $value2 = $this->getFloat($source->replaceVariables($this->getValue2()));
        $resultName = $source->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $result = match ($operator) {
            self::ADDITION => $value1 + $value2,
            self::SUBTRACTION => $value1 - $value2,
            self::MULTIPLICATION => $value1 * $value2,
            self::DIVISION => $value1 / $this->getFloat($value2, exclude: [0.0]),
            self::MODULO => $value1 % $this->getFloat($value2, exclude: [0.0]),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));

        yield Await::ALL;
        return $result;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleNumberInput("@action.fourArithmeticOperations.form.value1", "10", $this->getValue1(), true),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleNumberInput("@action.fourArithmeticOperations.form.value2", "50", $this->getValue2(), true),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ]);
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
