<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;

class Wait extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(private string $time = "") {
        parent::__construct(self::ACTION_WAIT, FlowItemCategory::SCRIPT);
    }

    public function getDetailDefaultReplaces(): array {
        return ["time"];
    }

    public function getDetailReplaces(): array {
        return [$this->getTime()];
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
