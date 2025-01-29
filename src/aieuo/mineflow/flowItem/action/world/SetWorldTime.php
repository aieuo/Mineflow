<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\World;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class SetWorldTime extends SimpleAction {

    public function __construct(
        string $worldName = "",
        int    $time = null
    ) {
        parent::__construct(self::SET_WORLD_TIME, FlowItemCategory::WORLD);

        $this->setArguments([
            WorldArgument::create("world", $worldName),
            NumberArgument::create("time", $time)->min(0)->max(World::TIME_FULL)->example("12000"),
        ]);
    }

    public function getWorld(): WorldArgument {
        return $this->getArgument("world");
    }

    public function getTime(): NumberArgument {
        return $this->getArgument("time");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $world = $this->getWorld()->getWorld($source);
        $time = $this->getTime()->getInt($source);

        $world->setTime($time);

        yield Await::ALL;
    }
}