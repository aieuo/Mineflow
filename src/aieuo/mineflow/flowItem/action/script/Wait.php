<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Wait extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(private string $time = "") {
        parent::__construct(self::ACTION_WAIT, FlowItemCategory::SCRIPT);
    }

    public function getDetailDefaultReplaces(): array {
        return ["time"];
    }

    public function getDetailReplaces(): array {
        return [$this->getTime()];
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->getTime() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $time = $this->getFloat($source->replaceVariables($this->getTime()), 1 / 20);

        yield from Await::promise(function ($resolve) use($time) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($resolve), (int)($time * 20));
        });
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.wait.form.time", "10", $this->getTime(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setTime($content[0]);
    }

    public function serializeContents(): array {
        return [$this->getTime()];
    }
}
