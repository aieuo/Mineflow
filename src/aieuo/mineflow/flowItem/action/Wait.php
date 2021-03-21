<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\scheduler\ClosureTask;

class Wait extends FlowItem {

    protected $id = self::ACTION_WAIT;

    protected $name = "action.wait.name";
    protected $detail = "action.wait.detail";
    protected $detailDefaultReplace = ["time"];

    protected $category = Category::SCRIPT;

    /** @var string */
    private $time;

    public function __construct(string $time = "") {
        $this->time = $time;
    }

    public function setTime(string $time): self {
        $this->time = $time;
        return $this;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->getTime() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTime()]);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $time = $source->replaceVariables($this->getTime());
        $this->throwIfInvalidNumber($time, 1 / 20);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick) use ($source): void {
                $source->resume();
            }
        ), (int)((float)$time * 20));
        yield false;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.wait.form.time", "10", $this->getTime(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setTime($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getTime()];
    }
}
