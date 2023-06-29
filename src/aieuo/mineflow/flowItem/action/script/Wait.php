<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Wait extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private NumberArgument $time;

    public function __construct(string $time = "") {
        parent::__construct(self::ACTION_WAIT, FlowItemCategory::SCRIPT);

        $this->time = new NumberArgument("time", $time, example: "10", min: 1 / 20);
    }

    public function getDetailDefaultReplaces(): array {
        return ["time"];
    }

    public function getDetailReplaces(): array {
        return [$this->time->get()];
    }

    public function getTime(): NumberArgument {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->time->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $time = $this->time->getFloat($source);

        yield from Await::promise(function ($resolve) use($time) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($resolve), (int)($time * 20));
        });
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->time->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->time->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->time->get()];
    }
}
