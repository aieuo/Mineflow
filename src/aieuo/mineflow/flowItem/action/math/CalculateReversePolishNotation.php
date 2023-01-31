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
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class CalculateReversePolishNotation extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        private string $formula = "",
        private string $resultName = "result"
    ) {
        parent::__construct(self::REVERSE_POLISH_NOTATION, FlowItemCategory::MATH);
    }

    public function getDetailDefaultReplaces(): array {
        return ["formula", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getFormula(), $this->getResultName()];
    }

    public function setFormula(string $formula): void {
        $this->formula = $formula;
    }

    public function getFormula(): string {
        return $this->formula;
    }

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getFormula() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $formula = $source->replaceVariables($this->getFormula());
        $resultName = $source->replaceVariables($this->getResultName());

        $stack = [];
        foreach (explode(" ", $formula) as $token) {
            if (is_numeric($token)) {
                $stack[] = (float)$token;
                continue;
            }

            $value2 = array_pop($stack);
            $value1 = array_pop($stack);
            $res = match ($token) {
                '+' => $value1 + $value2,
                '-' => $value1 - $value2,
                '*' => $value1 * $value2,
                '/' => $value1 / $value2,
                '%' => $value1 % $value2,
                default => throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$token])),
            };
            $stack[] = $res;
        }
        $result = $stack[0];

        $source->addVariable($resultName, new NumberVariable($result));

        yield Await::ALL;
        return $result;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.calculateRPN.form.value", "1 2 + 3 -", $this->getFormula(), true),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setFormula($content[0]);
        $this->setResultName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getFormula(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
