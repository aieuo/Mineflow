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

    private NumberArgument $time;

    public function __construct(string $time = "") {
        parent::__construct(self::ACTION_WAIT, FlowItemCategory::SCRIPT);

        $this->setArguments([
            $this->time = new NumberArgument("time", $time, example: "10", min: 1 / 20),
        ]);
    }

    public function getTime(): NumberArgument {
        return $this->time;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $time = $this->time->getFloat($source);

        yield from Await::promise(function ($resolve) use($time) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($resolve), (int)($time * 20));
        });
    }
}
