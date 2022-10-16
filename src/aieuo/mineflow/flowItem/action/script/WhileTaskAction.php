<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class WhileTaskAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    private int $limit = -1;

    private int $loopCount = 0;

    public function __construct(
        array       $conditions = [],
        array       $actions = [],
        private int $interval = 20,
        ?string     $customName = null
    ) {
        parent::__construct(self::ACTION_WHILE_TASK, FlowItemCategory::SCRIPT);

        $this->setConditions($conditions);
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_LOOP];
    }

    public function setLimit(int $limit): void {
        $this->limit = $limit;
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function setInterval(int $interval): void {
        $this->interval = $interval;
    }

    public function getInterval(): int {
        return $this->interval;
    }

    public function getDetail(): string {
        $details = ["", "§7========§f whileTask(".$this->getInterval().") §7========§f"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "§7~~~~~~~~~~~~~~~~~~~~~~~~~~~§f";
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $wait = new Wait((string)($this->getInterval() / 20));
        while (true) {
            $source->addVariable("i", new NumberVariable($this->loopCount)); // TODO: i を変更できるようにする
            foreach ($this->getConditions() as $i => $condition) {
                if (!(yield from $condition->execute($source))) {
                    break 2;
                }
            }

            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->getGenerator();
            yield from $wait->execute($source);
        }

        yield Await::ALL;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@condition.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION)),
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.whileTask.editInterval", fn(Player $player) => $this->sendSetWhileIntervalForm($player)),
        ];
    }

    public function sendSetWhileIntervalForm(Player $player): void {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new ExampleNumberInput("@action.whileTask.interval", "20", (string)$this->getInterval(), true, 1),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.cancelled"]);
                    return;
                }

                $this->setInterval((int)$data[0]);
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $condition = FlowItem::loadEachSaveData($content);
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        $this->setInterval($contents[2] ?? 20);
        $this->setLimit($contents[3] ?? -1);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getConditions(),
            $this->getActions(),
            $this->interval,
            $this->limit,
        ];
    }

    public function getAddingVariables(): array {
        return [
            "i" => new DummyVariable(NumberVariable::class)
        ];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getConditions() as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setConditions($conditions);

        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
