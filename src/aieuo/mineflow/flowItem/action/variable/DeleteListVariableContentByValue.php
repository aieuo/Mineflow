<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class DeleteListVariableContentByValue extends SimpleAction {

    public function __construct(string $variableName = "", string $variableValue = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_LIST_VARIABLE_CONTENT_BY_VALUE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("value", $variableValue, "@action.variable.form.value")->example("auieo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getVariableValue(): StringArgument {
        return $this->getArgument("value");
    }

    public function getIsLocal(): IsLocalVariableArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);

        $value = $this->getVariableValue()->getRawString();
        if ($helper->isVariableString($value)) {
            $value = $source->getVariable(mb_substr($value, 1, -1)) ?? VariableRegistry::global()->getNested(mb_substr($value, 1, -1));
            if ($value === null) {
                throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
            }
        } else {
            $value = new StringVariable($this->getVariableValue()->getString($source));
        }

        $variable = $source->getVariable($name) ?? ($this->getIsLocal()->getBool() ? null : VariableRegistry::global()->getNested($name));
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof IteratorVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $variable->removeValue($value, false);

        yield Await::ALL;
    }
}