<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class CalculateReversePolishNotation extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $formula = "", string $resultName = "result") {
        parent::__construct(self::REVERSE_POLISH_NOTATION, FlowItemCategory::MATH);

        $this->setArguments([
            new StringArgument("formula", $formula, example: "1 2 + 3 -"),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result"),
        ]);
    }

    public function getFormula(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $formula = $this->getFormula()->getString($source);
        $resultName = $this->getResultName()->getString($source);

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

    public function getAddingVariables(): array {
        return [
            $this->getResultName()->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
