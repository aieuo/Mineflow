<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\WorldFlowItem;
use aieuo\mineflow\flowItem\base\WorldFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Await;

class SetWorldTime extends FlowItem implements WorldFlowItem {
    use WorldFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $worldName = "",
        private string $time = ""
    ) {
        parent::__construct(self::SET_WORLD_TIME, FlowItemCategory::WORLD);

        $this->setWorldVariableName($worldName);
    }

    public function getDetailDefaultReplaces(): array {
        return ["world", "time"];
    }

    public function getDetailReplaces(): array {
        return [$this->getWorldVariableName(), $this->getTime()];
    }

    public function getTime(): string {
        return $this->time;
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function isDataValid(): bool {
        return $this->getWorldVariableName() !== "" and $this->getTime() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $world = $this->getWorld($source);
        $time = $this->getInt($source->replaceVariables($this->getTime()), 0, World::TIME_FULL);

        $world->setTime($time);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new WorldVariableDropdown($variables, $this->getWorldVariableName()),
            new ExampleNumberInput("@action.setWorldTime.form.time", "12000", $this->getTime(), required: true, min: 0, max: World::TIME_FULL),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setWorldVariableName($content[0]);
        $this->setTime($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getWorldVariableName(), $this->getTime()];
    }
}
