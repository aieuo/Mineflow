<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class CalculateReversePolishNotation extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $formula;
    private StringArgument $resultName;

    public function __construct(string $formula = "", string $resultName = "result") {
        parent::__construct(self::REVERSE_POLISH_NOTATION, FlowItemCategory::MATH);

        $this->formula = new StringArgument("formula", $formula, example: "1 2 + 3 -");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result");
    }

    public function getDetailDefaultReplaces(): array {
        return ["formula", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->formula->get(), $this->resultName->get()];
    }

    public function getFormula(): StringArgument {
        return $this->formula;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->formula->isNotEmpty() and $this->resultName->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $formula = $this->formula->getString($source);
        $resultName = $this->resultName->getString($source);

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
            $this->formula->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->formula->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->formula->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
