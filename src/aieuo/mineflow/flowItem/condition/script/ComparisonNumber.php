<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class ComparisonNumber extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public const EQUAL = 0;
    public const NOT_EQUAL = 1;
    public const GREATER = 2;
    public const LESS = 3;
    public const GREATER_EQUAL = 4;
    public const LESS_EQUAL = 5;

    private array $operatorSymbols = ["==", "!=", ">", "<", ">=", "<="];

    private NumberArgument $value1;
    private NumberArgument $value2;

    public function __construct(
        string      $value1 = "",
        private int $operator = self::EQUAL,
        string      $value2 = ""
    ) {
        parent::__construct(self::COMPARISON_NUMBER, FlowItemCategory::SCRIPT);

        $this->value1 = new NumberArgument("value1", $value1, example: "10");
        $this->value2 = new NumberArgument("value2", $value2, example: "50");
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "operator", "value2"];
    }

    public function getDetailReplaces(): array {
        return [$this->value1->get(), $this->operatorSymbols[$this->getOperator()], $this->value2->get()];
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

    public function isDataValid(): bool {
        return $this->value1->isValid() and $this->value2->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getFloat($source);
        $value2 = $this->value2->getFloat($source);
        $operator = $this->getOperator();

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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->value1->createFormElement($variables),
            new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
            $this->value2->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->value1->set($content[0]);
        $this->setOperator($content[1]);
        $this->value2->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->value1->get(), $this->getOperator(), $this->value2->get()];
    }
}
