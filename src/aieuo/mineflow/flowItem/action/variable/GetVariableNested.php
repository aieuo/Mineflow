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
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class GetVariableNested extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $variableName = "", string $resultName = "var",string $fallbackValue = "") {
        parent::__construct(self::GET_VARIABLE_NESTED, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName)->example("target.hand"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("item"),
            StringArgument::create("fallback", $fallbackValue)->optional()->example("optional"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    public function getFallbackValue(): StringArgument {
        return $this->getArgument("fallback");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->getVariableName()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $variable = $source->getVariable($variableName) ?? VariableRegistry::global()->getNested($variableName);

        $fallbackValue = $this->getFallbackValue()->getRawString();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Mineflow::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source->getVariableRegistryCopy());
        }

        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$variableName]));
        }

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(UnknownVariable::class)
        ];
    }
}