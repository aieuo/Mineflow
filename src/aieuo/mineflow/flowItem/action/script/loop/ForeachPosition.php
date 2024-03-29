<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class ForeachPosition extends SimpleAction {

    public function __construct(string $pos1 = "pos1", string $pos2 = "pos2", array $actions = [], ?string $customName = null) {
        parent::__construct(self::FOREACH_POSITION, FlowItemCategory::SCRIPT_LOOP, [FlowItemPermission::LOOP]);
        $this->setCustomName($customName);

        $this->setArguments([
            ActionArrayArgument::create("actions", $actions),
            PositionArgument::create("pos1", $pos1, "@action.foreachPosition.form.pos1"),
            PositionArgument::create("pos2", $pos2, "@action.foreachPosition.form.pos2"),
            StringArgument::create("current", "pos", "@action.foreachPosition.form.current"),
        ]);
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getPosition1(): PositionArgument {
        return $this->getArgument("pos1");
    }

    public function getPosition2(): PositionArgument {
        return $this->getArgument("pos2");
    }

    public function getCounterName(): StringArgument {
        return $this->getArgument("current");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $counterName = $this->getCounterName()->getString($source);
        $pos1 = $this->getPosition1()->getPosition($source);
        $pos2 = $this->getPosition2()->getPosition($source);

        [$sx, $ex] = [min($pos1->x, $pos2->x), max($pos1->x, $pos2->x)];
        [$sy, $ey] = [min($pos1->y, $pos2->y), max($pos1->y, $pos2->y)];
        [$sz, $ez] = [min($pos1->z, $pos2->z), max($pos1->z, $pos2->z)];

        for ($x = $sx; $x <= $ex; $x++) {
            for ($y = $sy; $y <= $ey; $y++) {
                for ($z = $sz; $z <= $ez; $z++) {
                    $pos = new Position($x, $y, $z, $pos1->getWorld());

                    yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [
                        $counterName => new PositionVariable($pos)
                    ], $source))->getGenerator();
                }
            }
        }

        yield Await::ALL;
    }

    public function getEditors(): array {
        return [
            new ActionArrayEditor($this->getActions()),
            new MainFlowItemEditor($this, [
                $this->getPosition1(),
                $this->getPosition2(),
                $this->getCounterName(),
            ], "@action.for.setting"),
        ];
    }
}
