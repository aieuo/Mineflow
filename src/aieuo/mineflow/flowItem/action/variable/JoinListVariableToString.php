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
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class JoinListVariableToString extends SimpleAction {

    public function __construct(string $variableName = "", string $separator = "", string $resultName = "result") {
        parent::__construct(self::JOIN_LIST_VARIABLE_TO_STRING, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("separator", $separator)->optional()->example(", "),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("string"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getSeparator(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $separator = $this->getSeparator()->getString($source);
        $result = $this->getResultName()->getString($source);

        $variable = $source->getVariable($name) ?? VariableRegistry::global()->getNested($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof IteratorVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $strings = [];
        foreach ($variable->getIterator() as $value) {
            $strings[] = (string)$value;
        }
        $source->addVariable($result, new StringVariable(implode($separator, $strings)));

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
