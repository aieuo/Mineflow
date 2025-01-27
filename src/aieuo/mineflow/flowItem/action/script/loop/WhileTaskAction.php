<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\action\script\Wait;
use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\ConditionArrayArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\ConditionArrayEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class WhileTaskAction extends SimpleAction {

    private int $loopCount = 0;

    public function __construct(
        array   $conditions = [],
        array   $actions = [],
        int     $interval = 20,
        ?string $customName = null
    ) {
        parent::__construct(self::ACTION_WHILE_TASK, FlowItemCategory::SCRIPT_LOOP, [FlowItemPermission::LOOP]);
        $this->setCustomName($customName);

        $this->setArguments([
            ConditionArrayArgument::create("conditions", $conditions),
            ActionArrayArgument::create("actions", $actions),
            NumberArgument::create("interval", $interval, "@action.whileTask.interval")->min(1),
            NumberArgument::create("limit", -1, "@action.whileTask.limit")->min(-1),
        ]);
    }

    public function getConditions(): ConditionArrayArgument {
        return $this->getArgument("conditions");
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getInterval(): NumberArgument {
        return $this->getArgument("interval");
    }

    public function getLimit(): NumberArgument {
        return $this->getArgument("limit");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $interval = $this->getInterval()->getFloat($source) / 20;
        // TODO: limit

        $wait = new Wait($interval);
        while (true) {
            $source->addVariable("i", new NumberVariable($this->loopCount)); // TODO: i を変更できるようにする
            foreach ($this->getConditions()->getItems() as $i => $condition) {
                if (!(yield from $condition->execute($source))) {
                    break 2;
                }
            }

            yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [], $source))->getGenerator();
            yield from $wait->execute($source);
        }

        yield Await::ALL;
    }

    public function getEditors(): array {
        return [
            new ConditionArrayEditor($this->getConditions()),
            new ActionArrayEditor($this->getActions()),
            new MainFlowItemEditor($this, [
                $this->getInterval(),
            ], "@action.whileTask.editInterval"),
        ];
    }
}