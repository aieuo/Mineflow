<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class ForeachAction extends SimpleAction {

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_FOREACH, FlowItemCategory::SCRIPT_LOOP, [FlowItemPermission::LOOP]);
        $this->setCustomName($customName);

        $this->setArguments([
            ActionArrayArgument::create("actions", $actions),
            StringArrayArgument::create("list", "list", "@action.foreach.listVariableName")->example("list"),
            StringArrayArgument::create("key", "key", "@action.foreach.keyVariableName")->example("key"),
            StringArrayArgument::create("value", "value", "@action.foreach.valueVariableName")->example("value"),
        ]);
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getListVariableName(): StringArgument {
        return $this->getArgument("list");
    }

    public function getKeyVariableName(): StringArgument {
        return $this->getArgument("key");
    }

    public function getValueVariableName(): StringArgument {
        return $this->getArgument("value");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $listName = $this->getListVariableName()->getString($source);
        $keyName = $this->getKeyVariableName()->getString($source);
        $valueName = $this->getValueVariableName()->getString($source);
        $list = $source->getVariable($listName) ?? VariableRegistry::global()->getNested($listName);

        if (!($list instanceof IteratorVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.foreach.error.notVariable", [$listName]));
        }

        foreach ($list->getIterator() as $key => $value) {
            $keyVariable = is_numeric($key) ? new NumberVariable($key) : new StringVariable($key);
            $valueVariable = clone $value;

            yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [
                $keyName => $keyVariable,
                $valueName => $valueVariable
            ], $source))->getGenerator();
        }

        yield Await::ALL;
    }

    public function getEditors(): array {
        return [
            new ActionArrayEditor($this->getActions()),
            new MainFlowItemEditor($this, [
                $this->getListVariableName(),
                $this->getKeyVariableName(),
                $this->getValueVariableName(),
            ], "@action.for.setting"),
        ];
    }
}