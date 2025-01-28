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
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class Calculate2 extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const CALC_MIN = 0;
    public const CALC_MAX = 1;
    public const CALC_POW = 2;
    public const CALC_LOG = 3;
    public const CALC_HYPOT = 4;
    public const CALC_ATAN2 = 5;
    public const CALC_ROUND = 6;

    private array $operatorSymbols = [
        "min(x, y)",
        "max(x, y)",
        "x^y",
        "log_y(x)",
        "√(x^2 + y^2)",
        "atan2(x, y)",
        "round(x, y)"
    ];

    public function __construct(float $value1 = 0, float $value2 = 0, int $operator = self::CALC_MIN, string $resultName = "result") {
        parent::__construct(self::CALCULATE2, FlowItemCategory::MATH);

        $this->setArguments([
            NumberArgument::create("value1", $value1)->example("10"),
            NumberArgument::create("value2", $value2)->example("20"),
            IntEnumArgument::create("operator", $operator, "@action.fourArithmeticOperations.form.operator")->options($this->operatorSymbols),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("result"),
        ]);
    }

    public function getValue1(): NumberArgument {
        return $this->getArgument("value1");
    }

    public function getValue2(): NumberArgument {
        return $this->getArgument("value2");
    }

    public function getOperator(): IntEnumArgument {
        return $this->getArgument("operator");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->getValue1()->getFloat($source);
        $value2 = $this->getValue2()->getFloat($source);
        $resultName = $this->getResultName()->getString($source);
        $operator = $this->getOperator()->getEnumValue();

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

        yield Await::ALL;
        return $result;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}