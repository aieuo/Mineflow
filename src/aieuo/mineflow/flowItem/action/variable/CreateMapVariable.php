<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class CreateMapVariable extends SimpleAction {

    public function __construct(string $variableName = "", string $key = "", string $value = "", bool $isLocal = true) {
        parent::__construct(self::CREATE_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArrayArgument::create("key", $key, "@action.variable.form.key")->example("aieuo"),
            StringArrayArgument::create("value", $value, "@action.variable.form.value")->example("aieuo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getVariableKey(): StringArrayArgument {
        return $this->getArgument("key");
    }

    public function getVariableValue(): StringArrayArgument {
        return $this->getArgument("value");
    }

    public function getIsLocal(): IsLocalVariableArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $keys = $this->getVariableKey()->getArray($source);
        $values = $this->getVariableValue()->getRawArray();

        $variable = new MapVariable([]);
        for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
            $key = $keys[$i];
            $value = $values[$i] ?? "";
            if ($key === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source->getVariableRegistryCopy());
            $variable->setValueAt($key, $addVariable);
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