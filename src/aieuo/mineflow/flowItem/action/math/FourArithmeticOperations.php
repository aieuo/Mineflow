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
use aieuo\mineflow\utils\Utils;
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

    private array $operatorSymbols = ["+", "-", "*", "/", "%%"];

    private NumberArgument $value1;
    private NumberArgument $value2;
    private StringArgument $resultName;

    public function __construct(
        float       $value1 = null,
        private int $operator = self::ADDITION,
        float       $value2 = null,
        string      $resultName = "result"
    ) {
        parent::__construct(self::FOUR_ARITHMETIC_OPERATIONS, FlowItemCategory::MATH);

        $this->value1 = new NumberArgument("value1", $value1, example: "10");
        $this->value2 = new NumberArgument("value2", $value2, example: "50");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result");
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "value2", "operator", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->value1->getName(), $this->operatorSymbols[$this->getOperator()] ?? "?", $this->value2->getName(), $this->resultName->get()];
    }

    public function getValue1(): NumberArgument {
        return $this->value1;
    }

    public function setOperator(int $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function getValue2(): NumberArgument {
        return $this->value2;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->value1->isNotEmpty() and $this->value2->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getFloat($source);
        $value2 = $this->value2->getFloat($source);
        $resultName = $this->resultName->getString($source);
        $operator = $this->getOperator();

        $result = match ($operator) {
            self::ADDITION => $value1 + $value2,
            self::SUBTRACTION => $value1 - $value2,
            self::MULTIPLICATION => $value1 * $value2,
            self::DIVISION => $value1 / Utils::getFLoat($value2, exclude: [0.0]),
            self::MODULO => $value1 % Utils::getFLoat($value2, exclude: [0.0]),
            default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator])),
        };

        $source->addVariable($resultName, new NumberVariable($result));

        yield Await::ALL;
        return $result;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value1->createFormElement($variables),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            $this->value2->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->value1->set($content[0]);
        $this->value2->set($content[1]);
        $this->setOperator($content[1]);
        $this->resultName->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->value1->get(), $this->getOperator(), $this->value2->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
