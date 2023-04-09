<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\loop;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
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

class RepeatAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    private string $repeatCount;

    private string $startIndex = "0";
    private string $counterName = "i";

    public function __construct(array $actions = [], int $count = 1, ?string $customName = null) {
        parent::__construct(self::ACTION_REPEAT, FlowItemCategory::SCRIPT_LOOP);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->setActions($actions);
        $this->repeatCount = (string)$count;
        $this->setCustomName($customName);
    }

    public function setRepeatCount(string $count): void {
        $this->repeatCount = $count;
    }

    public function getRepeatCount(): string {
        return $this->repeatCount;
    }

    public function setStartIndex(string $startIndex): void {
        $this->startIndex = $startIndex;
    }

    public function getStartIndex(): string {
        return $this->startIndex;
    }

    public function setCounterName(string $counterName): void {
        $this->counterName = $counterName;
    }

    public function getCounterName(): string {
        return $this->counterName;
    }

    public function getDetail(): string {
        $repeat = $this->getRepeatCount();
        $length = strlen($repeat) - 1;
        $left = (int)ceil($length / 2);
        $right = $length - $left;
        $details = ["", "§7".str_repeat("=", 12 - $left)."§frepeat(".$repeat.")§7".str_repeat("=", 12 - $right)."§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getShortDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->getInt($source->replaceVariables($this->repeatCount), 1);
        $start = $this->getInt($source->replaceVariables($this->startIndex));

        $name = $this->counterName;
        $end = $start + $count;

        for ($i = $start; $i < $end; $i++) {
            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                $name => new NumberVariable($i)
            ], $source))->getGenerator();
        }

        return Await::ALL;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.for.setting", fn(Player $player) => $this->sendSetRepeatCountForm($player)),
        ];
    }

    public function sendSetRepeatCountForm(Player $player): void {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new ExampleNumberInput("@action.repeat.repeatCount", "10", $this->getRepeatCount(), true, 1),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.cancelled"]);
                    return;
                }

                $this->setRepeatCount($data[0]);
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): void {
        $this->setRepeatCount((string)$contents[0]);

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        if (isset($contents[2])) $this->startIndex = (string)$contents[2];
        if (isset($contents[3])) $this->counterName = $contents[3];
    }

    public function serializeContents(): array {
        return [
            $this->repeatCount,
            $this->getActions(),
            $this->startIndex,
            $this->counterName
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->getCounterName() => new DummyVariable(NumberVariable::class)
        ];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
