<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Await;

class SetWorldTime extends SimpleAction {

    private WorldArgument $world;
    private NumberArgument $time;

    public function __construct(
        string $worldName = "",
        int    $time = null
    ) {
        parent::__construct(self::SET_WORLD_TIME, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->world = new WorldArgument("world", $worldName),
            $this->time = new NumberArgument("time", $time, example: "12000", min: 0, max: World::TIME_FULL),
        ]);
    }

    public function getWorld(): WorldArgument {
        return $this->world;
    }

    public function getTime(): NumberArgument {
        return $this->time;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $world = $this->world->getWorld($source);
        $time = $this->time->getInt($source);

        $world->setTime($time);

        yield Await::ALL;
    }
}
