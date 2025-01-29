<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;
use function str_ends_with;

class ComparisonString extends SimpleCondition {

    public const EQUALS = 0;
    public const NOT_EQUALS = 1;
    public const CONTAINS = 2;
    public const NOT_CONTAINS = 3;
    public const STARTS_WITH = 4;
    public const ENDS_WITH = 5;

    private array $operatorSymbols = ["==", "!=", "contains", "not contains", "starts with", "ends with"];

    public function __construct(string $value1 = "", int $operator = self::EQUALS, string $value2 = "") {
        parent::__construct(self::COMPARISON_STRING, FlowItemCategory::SCRIPT);

        $this->setArguments([
            StringArgument::create("value1", $value1, "@condition.comparisonNumber.form.value1")->example("10"),
            IntEnumArgument::create("operator", $operator, "@condition.comparisonNumber.form.operator")->options($this->operatorSymbols),
            StringArgument::create("value2", $value2, "@condition.comparisonNumber.form.value2")->optional()->example("50"),
        ]);
    }

    public function getValue1(): StringArgument {
        return $this->getArgument("value1");
    }

    public function getOperator(): IntEnumArgument {
        return $this->getArgument("operator");
    }

    public function getValue2(): StringArgument {
        return $this->getArgument("value2");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->getValue1()->getString($source);
        $value2 = $this->getValue2()->getString($source);
        $operator = $this->getOperator()->getEnumValue();

        $result = match ($operator) {
            self::EQUALS => $value1 === $value2,
            self::NOT_EQUALS => $value1 !== $value2,
            self::CONTAINS => str_contains($value1, $value2),
            self::NOT_CONTAINS => !str_contains($value1, $value2),
            self::STARTS_WITH => str_starts_with($value1, $value2),
            self::ENDS_WITH => str_ends_with($value1, $value2),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        yield Await::ALL;
        return $result;
    }
}