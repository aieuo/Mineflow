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

    private StringArgument $variableName;
    private StringArgument $resultName;
    private StringArgument $fallbackValue;

    public function __construct(string $variableName = "", string $resultName = "var",string $fallbackValue = "") {
        parent::__construct(self::GET_VARIABLE_NESTED, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, example: "target.hand"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "item"),
            $this->fallbackValue = new StringArgument("fallback", $fallbackValue, example: "optional", optional: true),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function getFallbackValue(): StringArgument {
        return $this->fallbackValue;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->variableName->getString($source);
        $resultName = $this->resultName->getString($source);

        $variable = $source->getVariable($variableName) ?? Mineflow::getVariableHelper()->getNested($variableName);

        $fallbackValue = $this->fallbackValue->get();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Mineflow::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source);
        }

        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$variableName]));
        }

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(UnknownVariable::class)
        ];
    }
}
