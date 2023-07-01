<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class FourArithmeticOperations extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const ADDITION = 0;
    public const SUBTRACTION = 1;
    public const MULTIPLICATION = 2;
    public const DIVISION = 3;
    public const MODULO = 4;

    private array $operatorSymbols = ["+", "-", "*", "/", "%%"];

    private NumberArgument $value1;
    private IntEnumArgument $operator;
    private NumberArgument $value2;
    private StringArgument $resultName;

    public function __construct(float $value1 = null, int $operator = self::ADDITION, float $value2 = null, string $resultName = "result") {
        parent::__construct(self::FOUR_ARITHMETIC_OPERATIONS, FlowItemCategory::MATH);

        $this->setArguments([
            $this->value1 = new NumberArgument("value1", $value1, example: "10"),
            $this->operator = new IntEnumArgument("operator", $operator, $this->operatorSymbols),
            $this->value2 = new NumberArgument("value2", $value2, example: "50"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result"),
        ]);
    }

    public function getValue1(): NumberArgument {
        return $this->value1;
    }

    public function getOperator(): IntEnumArgument {
        return $this->operator;
    }

    public function getValue2(): NumberArgument {
        return $this->value2;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getFloat($source);
        $value2 = $this->value2->getFloat($source);
        $resultName = $this->resultName->getString($source);
        $operator = $this->operator->getValue();

        $result = match ($operator) {
            self::ADDITION => $value1 + $value2,
            self::SUBTRACTION => $value1 - $value2,
            self::MULTIPLICATION => $value1 * $value2,
            self::DIVISION => $value1 / Utils::getFloat($value2, exclude: [0.0]),
            self::MODULO => $value1 % Utils::getFloat($value2, exclude: [0.0]),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));

        yield Await::ALL;
        return $result;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
