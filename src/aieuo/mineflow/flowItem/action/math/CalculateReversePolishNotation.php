<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class CalculateReversePolishNotation extends FlowItem {

    protected string $id = self::REVERSE_POLISH_NOTATION;

    protected string $name = "action.calculateRPN.name";
    protected string $detail = "action.calculateRPN.detail";
    protected array $detailDefaultReplace = ["formula", "result"];

    protected string $category = FlowItemCategory::MATH;
    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private string $formula;
    private string $resultName;

    public function __construct(string $formula = "", string $resultName = "result") {
        $this->formula = $formula;
        $this->resultName = $resultName;
    }

    public function setFormula(string $formula): self {
        $this->formula = $formula;
        return $this;
    }

    public function getFormula(): string {
        return $this->formula;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getFormula() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getFormula(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

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
        yield true;
        return $result;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.calculateRPN.form.value", "1 2 + 3 -", $this->getFormula(), true),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFormula($content[0]);
        $this->setResultName($content[1]);
        return $this;
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
