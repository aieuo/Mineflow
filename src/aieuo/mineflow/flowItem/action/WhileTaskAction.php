<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\Player;

class WhileTaskAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_WHILE_TASK;

    protected $name = "action.whileTask.name";
    protected $detail = "action.whileTask.description";

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var int */
    private $interval;
    /** @var int */
    private $limit = -1;

    /** @var int */
    private $loopCount = 0;

    public function __construct(array $conditions = [], array $actions = [], int $interval = 20, ?string $customName = null) {
        $this->setItems($conditions, FlowItemContainer::CONDITION);
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->interval = $interval;
        $this->setCustomName($customName);
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
                    $source->resume();
                    return true;
                }
            }

            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
            yield from $wait->execute($source);
        }
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@condition.edit", function (Player $player) {
                (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION);
            }),
            new Button("@action.edit", function (Player $player) {
                (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION);
            }),
            new Button("@action.whileTask.editInterval", function (Player $player) {
                $this->sendSetWhileIntervalForm($player);
            }),
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
            $this->addItem($condition, FlowItemContainer::CONDITION);
        }

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
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
            "i" => new DummyVariable(DummyVariable::NUMBER)
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
        $this->setItems($conditions, FlowItemContainer::CONDITION);

        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}