<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class ComparisonNumber extends SimpleCondition {

    public const EQUAL = 0;
    public const NOT_EQUAL = 1;
    public const GREATER = 2;
    public const LESS = 3;
    public const GREATER_EQUAL = 4;
    public const LESS_EQUAL = 5;

    private array $operatorSymbols = ["==", "!=", ">", "<", ">=", "<="];

    private NumberArgument $value1;
    private IntEnumArgument $operator;
    private NumberArgument $value2;

    public function __construct(string $value1 = "", int $operator = self::EQUAL, string $value2 = "") {
        parent::__construct(self::COMPARISON_NUMBER, FlowItemCategory::SCRIPT);

        $this->setArguments([
            $this->value1 = new NumberArgument("value1", $value1, example: "10"),
            $this->operator = new IntEnumArgument("operator", $operator, $this->operatorSymbols, "@condition.comparisonNumber.form.operator"),
            $this->value2 = new NumberArgument("value2", $value2, example: "50"),
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getFloat($source);
        $value2 = $this->value2->getFloat($source);
        $operator = $this->operator->getValue();

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
}
