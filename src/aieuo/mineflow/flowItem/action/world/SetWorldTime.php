<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Await;

class SetWorldTime extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private WorldArgument $world;
    private NumberArgument $time;

    public function __construct(
        string $worldName = "",
        int    $time = null
    ) {
        parent::__construct(self::SET_WORLD_TIME, FlowItemCategory::WORLD);

        $this->world = new WorldArgument("world", $worldName);
        $this->time = new NumberArgument("time", $time, example: "12000", min: 0, max: World::TIME_FULL);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->world->getName(), "time"];
    }

    public function getDetailReplaces(): array {
        return [$this->world->get(), $this->time->get()];
    }

    public function getWorld(): WorldArgument {
        return $this->world;
    }

    public function getTime(): NumberArgument {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->world->isNotEmpty() and $this->time->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $world = $this->world->getWorld($source);
        $time = $this->time->getInt($source);

        $world->setTime($time);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->world->createFormElement($variables),
            $this->time->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->world->set($content[0]);
        $this->time->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->world->get(), $this->time->get()];
    }
}
