<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\action\script\Wait;
use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\ConditionArrayArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
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

class WhileTaskAction extends FlowItem {

    private int $loopCount = 0;

    public function __construct(
        array   $conditions = [],
        array   $actions = [],
        int     $interval = 20,
        ?string $customName = null
    ) {
        parent::__construct(self::ACTION_WHILE_TASK, FlowItemCategory::SCRIPT_LOOP);
        $this->setPermissions([FlowItemPermission::LOOP]);
        $this->setCustomName($customName);

        $this->setArguments([
            ConditionArrayArgument::create("conditions", $conditions),
            ActionArrayArgument::create("actions", $actions),
            NumberArgument::create("interval", $interval, "@action.whileTask.interval")->min(1),
            NumberArgument::create("limit", -1, "@action.whileTask.limit")->min(-1),
        ]);
    }

    public function getName(): string {
        return Language::get("action.whileTask.name");
    }

    public function getDescription(): string {
        return Language::get("action.whileTask.description");
    }

    public function getDetail(): string {
        return <<<END
            
            §7========§f whileTask({$this->getInterval()}) §7========§f
            {$this->getConditions()}
            §7~~~~~~~~~~~~~~~~~~~~~~~~~~~§f
            {$this->getActions()}
            §7================================§f
            END;
    }

    public function getConditions(): ConditionArrayArgument {
        return $this->getArguments()[0];
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArguments()[1];
    }

    public function getInterval(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getLimit(): NumberArgument {
        return $this->getArguments()[3];
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
