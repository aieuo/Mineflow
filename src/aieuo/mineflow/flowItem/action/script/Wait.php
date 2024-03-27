<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Wait extends SimpleAction {

    public function __construct(float $time = null) {
        parent::__construct(self::ACTION_WAIT, FlowItemCategory::SCRIPT);

        $this->setArguments([
            NumberArgument::create("time", $time)->min(1 / 20)->example("10"),
        ]);
    }

    public function getTime(): NumberArgument {
        return $this->getArgument("time");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $time = $this->getTime()->getFloat($source);

        yield from Await::promise(function ($resolve) use($time) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($resolve), (int)($time * 20));
        });
    }
}
