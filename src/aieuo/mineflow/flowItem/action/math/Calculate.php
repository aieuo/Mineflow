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
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;
use function abs;

class Calculate extends SimpleAction {

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

    private array $operatorSymbols = ["x^2", "√x", "x!", "abs(x)", "log(x)", "sin(x)", "cos(x)", "tan(x)", "asin(x)", "acos(x)", "atan(x)", "deg2rad(x)", "rad2deg(x)", "floor(x)", "round(x)", "ceil(x)"];

    public function __construct(string $value = "", int $operator = self::SQUARE, string $resultName = "result") {
        parent::__construct(self::CALCULATE, FlowItemCategory::MATH);

        $this->setArguments([
            NumberArgument::create("value", $value)->example("10"),
            IntEnumArgument::create("operator", $operator, "@action.fourArithmeticOperations.form.operator")->options($this->operatorSymbols),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("result"),
        ]);
    }

    public function getValue(): NumberArgument {
        return $this->getArgument("value");
    }

    public function getOperator(): IntEnumArgument {
        return $this->getArgument("operator");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->getValue()->getFloat($source);
        $resultName = $this->getResultName()->getString($source);
        $operator = $this->getOperator()->getEnumValue();

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

        yield Await::ALL;
        return $result;
    }

    private function factorial(int $value): int {
        $result = 1;
        for ($i = abs($value); $i > 1; $i--) {
            $result *= $i;
        }
        return $result;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
