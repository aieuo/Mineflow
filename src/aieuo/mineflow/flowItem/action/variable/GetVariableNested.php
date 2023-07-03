<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use SOFe\AwaitGenerator\Await;

class GetVariableNested extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $variableName = "", string $resultName = "var",string $fallbackValue = "") {
        parent::__construct(self::GET_VARIABLE_NESTED, FlowItemCategory::VARIABLE);

        $this->setArguments([
            new StringArgument("name", $variableName, example: "target.hand"),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "item"),
            new StringArgument("fallback", $fallbackValue, example: "optional", optional: true),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getFallbackValue(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->getVariableName()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $variable = $source->getVariable($variableName) ?? Mineflow::getVariableHelper()->getNested($variableName);

        $fallbackValue = $this->getFallbackValue()->get();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Mineflow::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source);
        }

        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$variableName]));
        }

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->getResultName()->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName()->get() => new DummyVariable(UnknownVariable::class)
        ];
    }
}
