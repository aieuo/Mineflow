<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class Calculate2 extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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

    private NumberArgument $value1;
    private NumberArgument $value2;
    private StringArgument $resultName;

    public function __construct(float $value1 = 0, float $value2 = 0, string $operator = null, string $resultName = "result") {
        parent::__construct(self::CALCULATE2, FlowItemCategory::MATH);

        $this->value1 = new NumberArgument("value1", $value1, example: "10");
        $this->value2 = new NumberArgument("value2", $value2, example: "20");
        $this->operator = (int)($operator ?? self::CALC_MIN);
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result");
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "value2", "operator", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->value1->get(), $this->value2->get(), $this->operatorSymbols[$this->getOperator()], $this->resultName];
    }

    public function getValue1(): NumberArgument {
        return $this->value1;
    }

    public function getValue2(): NumberArgument {
        return $this->value2;
    }

    public function setOperator(int $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->value1->get() !== "" and $this->value2->get() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getFloat($source);
        $value2 = $this->value2->getFloat($source);
        $resultName = $this->resultName->getString($source);
        $operator = $this->getOperator();

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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value1->createFormElement($variables),
            $this->value2->createFormElement($variables),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->value1->set($content[0]);
        $this->value2->set($content[1]);
        $this->setOperator($content[2]);
        $this->resultName->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->value1->get(), $this->value2->get(), $this->getOperator(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
