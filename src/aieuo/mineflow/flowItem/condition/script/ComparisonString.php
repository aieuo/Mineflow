<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
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
use function str_ends_with;

class ComparisonString extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public const EQUALS = 0;
    public const NOT_EQUALS = 1;
    public const CONTAINS = 2;
    public const NOT_CONTAINS = 3;
    public const STARTS_WITH = 4;
    public const ENDS_WITH = 5;

    private int $operator;

    private array $operatorSymbols = ["==", "!=", "contains", "not contains", "starts with", "ends with"];

    private StringArgument $value1;
    private StringArgument $value2;

    public function __construct(string $value1 = "", string $operator = null, string $value2 = "") {
        parent::__construct(self::COMPARISON_STRING, FlowItemCategory::SCRIPT);

        $this->value1 = new StringArgument("value1", $value1, "@condition.comparisonNumber.form.value1", example: "10");
        $this->operator = (int)($operator ?? self::EQUALS);
        $this->value2 = new StringArgument("value2", $value2, "@condition.comparisonNumber.form.value2", example: "50", optional: true);
    }

    public function getDetailDefaultReplaces(): array {
        return ["value1", "operator", "value2"];
    }

    public function getDetailReplaces(): array {
        return [$this->value1->get(), $this->operatorSymbols[$this->getOperator()], $this->value2->get()];
    }

    public function getValue1(): StringArgument {
        return $this->value1;
    }

    public function getValue2(): StringArgument {
        return $this->value2;
    }

    public function setOperator(int $operator): void {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function isDataValid(): bool {
        return $this->value1->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value1 = $this->value1->getString($source);
        $value2 = $this->value2->getString($source);
        $operator = $this->getOperator();

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
