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
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class JoinListVariableToString extends SimpleAction {

    private StringArgument $variableName;
    private StringArgument $separator;
    private StringArgument $resultName;

    public function __construct(string $variableName = "", string $separator = "", string $resultName = "result") {
        parent::__construct(self::JOIN_LIST_VARIABLE_TO_STRING, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->separator = new StringArgument("separator", $separator, example: ", ", optional: true),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "string"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getSeparator(): StringArgument {
        return $this->separator;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $separator = $this->separator->getString($source);
        $result = $this->resultName->getString($source);

        $variable = $source->getVariable($name) ?? $helper->getNested($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $strings = [];
        foreach ($variable->getValue() as $key => $value) {
            $strings[] = (string)$value;
        }
        $source->addVariable($result, new StringVariable(implode($separator, $strings)));

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
