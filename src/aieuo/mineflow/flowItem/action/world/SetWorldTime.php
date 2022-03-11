<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\WorldFlowItem;
use aieuo\mineflow\flowItem\base\WorldFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\world\World;

class SetWorldTime extends FlowItem implements WorldFlowItem {
    use WorldFlowItemTrait;

    protected string $id = self::SET_WORLD_TIME;

    protected string $name = "action.setWorldTime.name";
    protected string $detail = "action.setWorldTime.detail";
    protected array $detailDefaultReplace = ["world", "time"];

    protected string $category = FlowItemCategory::WORLD;

    public function __construct(
        string         $worldName = "",
        private string $time = ""
    ) {
        $this->setWorldVariableName($worldName);
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getWorldVariableName(), $this->getTime()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $world = $this->getWorld($source);
        $time = $source->replaceVariables($this->getTime());

        $this->throwIfInvalidNumber($time, 0, World::TIME_FULL);

        $world->setTime((int)$time);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new WorldVariableDropdown($variables, $this->getWorldVariableName()),
            new ExampleNumberInput("@action.setWorldTime.form.time", "minecraft:explosion_particle", $this->getTime(), required: true, min: 0, max: World::TIME_FULL),
        ];
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