<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class RepeatAction extends SimpleAction {

    public function __construct(array $actions = [], int $count = 1, int $start = 0) {
        parent::__construct(self::ACTION_REPEAT, FlowItemCategory::SCRIPT_LOOP, [FlowItemPermission::LOOP]);

        $this->setArguments([
            NumberArgument::create("repeat", $count, "@action.repeat.repeatCount")->min(1)->example("10"),
            ActionArrayArgument::create("actions", $actions),
            NumberArgument::create("start", $start, "@action.repeat.start")->example("0"),
            StringArgument::create("counter", "i", "@action.for.counterName")->example("1"),
        ]);
    }

    public function getRepeatCount(): NumberArgument {
        return $this->getArgument("repeat");
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getStartIndex(): NumberArgument {
        return $this->getArgument("start");
    }

    public function getCounterName(): StringArgument {
        return $this->getArgument("counter");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->getRepeatCount()->getInt($source);
        $start = $this->getStartIndex()->getInt($source);
        $name = $this->getCounterName()->getString($source);

        $end = $start + $count;

        for ($i = $start; $i < $end; $i++) {
            yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [
                $name => new NumberVariable($i)
            ], $source))->getGenerator();
        }

        return Await::ALL;
    }

    public function getEditors(): array {
        return [
            new ActionArrayEditor($this->getActions()),
            new MainFlowItemEditor($this, [
                $this->getRepeatCount(),
                $this->getStartIndex(),
                $this->getCounterName(),
            ], "@action.for.setting"),
        ];
    }
}