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
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use SOFe\AwaitGenerator\Await;
use function array_is_list;

class CreateMapVariableFromJson extends SimpleAction {

    public function __construct(string $variableName = "", string $json = "", bool $isLocal = true) {
        parent::__construct(self::CREATE_MAP_VARIABLE_FROM_JSON, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("json", $json, "@action.variable.form.value")->example("aeiuo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getJson(): StringArgument {
        return $this->getArgument("json");
    }

    public function getIsLocal(): IsLocalVariableArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $json = $this->getJson()->getRawString();

        $value = json_decode($json, true);
        if ($value === null) {
            throw new InvalidFlowValueException($this->getName(), json_last_error_msg());
        }

        if (array_is_list($value)) {
            $variable = new ListVariable(Mineflow::getVariableHelper()->toVariableArray($value));
        } else {
            $variable = new MapVariable(Mineflow::getVariableHelper()->toVariableArray($value));
        }

        if ($this->getIsLocal()->getBool()) {
            $source->addVariable($name, $variable);
        } else {
            VariableRegistry::global()->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(MapVariable::class)
        ];
    }
}