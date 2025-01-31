<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class ForAction extends SimpleAction {

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_FOR, FlowItemCategory::SCRIPT_LOOP, [FlowItemPermission::LOOP]);
        $this->setCustomName($customName);

        $this->setArguments([
            ActionArrayArgument::create("actions", $actions),
            StringArgument::create("counter", "i", "@action.for.counterName")->example("i"),
            NumberArgument::create("start", "0", "@action.for.start")->example("0"),
            NumberArgument::create("end", "10", "@action.for.end")->example("10"),
            NumberArgument::create("steps", "1", "@action.for.fluctuation")->excludes([0.0])->example("1"),
        ]);
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getCounterName(): StringArgument {
        return $this->getArgument("counter");
    }

    public function getStartIndex(): NumberArgument {
        return $this->getArgument("start");
    }

    public function getEndIndex(): NumberArgument {
        return $this->getArgument("end");
    }

    public function getSteps(): NumberArgument {
        return $this->getArgument("steps");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $counterName = $this->getCounterName()->getString($source);
        $start = $this->getStartIndex()->getFloat($source);
        $end = $this->getEndIndex()->getFloat($source);
        $steps = $this->getSteps()->getFloat($source);

        for ($i = $start; $i <= $end; $i += $steps) {
            yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [
                $counterName => new NumberVariable($i)
            ], $source))->getGenerator();
        }

        yield Await::ALL;
    }

    public function getEditors(): array {
        return [
            new ActionArrayEditor($this->getActions()),
            new MainFlowItemEditor($this, [
                $this->getCounterName(),
                $this->getStartIndex(),
                $this->getEndIndex(),
                $this->getSteps(),
            ], "@action.for.setting"),
        ];
    }
}