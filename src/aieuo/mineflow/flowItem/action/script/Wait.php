<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\scheduler\ClosureTask;

class Wait extends FlowItem {

    protected string $id = self::ACTION_WAIT;

    protected string $name = "action.wait.name";
    protected string $detail = "action.wait.detail";
    protected array $detailDefaultReplace = ["time"];

    protected string $category = Category::SCRIPT;

    private string $time;

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $time = $source->replaceVariables($this->getTime());
        $this->throwIfInvalidNumber($time, 1 / 20);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function () use ($source): void {
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
