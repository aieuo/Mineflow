<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\WorldPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Await;

class SetWorldTime extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private WorldPlaceholder $world;

    public function __construct(
        string         $worldName = "",
        private string $time = ""
    ) {
        parent::__construct(self::SET_WORLD_TIME, FlowItemCategory::WORLD);

        $this->world = new WorldPlaceholder("world", $worldName);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->world->getName(), "time"];
    }

    public function getDetailReplaces(): array {
        return [$this->world->get(), $this->getTime()];
    }

    public function getWorld(): WorldPlaceholder {
        return $this->world;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function isDataValid(): bool {
        return $this->world->isNotEmpty() and $this->getTime() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $world = $this->world->getWorld($source);
        $time = $this->getInt($source->replaceVariables($this->getTime()), 0, World::TIME_FULL);

        $world->setTime($time);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->world->createFormElement($variables),
            new ExampleNumberInput("@action.setWorldTime.form.time", "12000", $this->getTime(), required: true, min: 0, max: World::TIME_FULL),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->world->set($content[0]);
        $this->setTime($content[1]);
    }

    public function serializeContents(): array {
        return [$this->world->get(), $this->getTime()];
    }
}
