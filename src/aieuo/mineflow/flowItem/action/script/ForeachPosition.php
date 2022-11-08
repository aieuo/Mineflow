<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\player\Player;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class ForeachPosition extends FlowItem implements FlowItemContainer, PositionFlowItem {
    use FlowItemContainerTrait, PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    private string $counterName = "pos";

    public function __construct(string $pos1 = "pos1", string $pos2 = "pos2", array $actions = [], ?string $customName = null) {
        parent::__construct(self::FOREACH_POSITION, FlowItemCategory::SCRIPT_LOOP);

        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_LOOP];
    }

    public function getDetail(): string {
        $repeat = $this->getPositionVariableName("pos1")." -> ".$this->getPositionVariableName("pos2")."; (".$this->counterName.")";

        $details = ["", "§7==§f eachPos(".$repeat.") §7==§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $counterName = $source->replaceVariables($this->counterName);

        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");

        [$sx, $ex] = [min($pos1->x, $pos2->x), max($pos1->x, $pos2->x)];
        [$sy, $ey] = [min($pos1->y, $pos2->y), max($pos1->y, $pos2->y)];
        [$sz, $ez] = [min($pos1->z, $pos2->z), max($pos1->z, $pos2->z)];

        for ($x = $sx; $x <= $ex; $x++) {
            for ($y = $sy; $y <= $ey; $y++) {
                for ($z = $sz; $z <= $ez; $z++) {
                    $pos = new Position($x, $y, $z, $pos1->getWorld());

                    yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                        $counterName => new PositionVariable($pos, $counterName)
                    ], $source))->getGenerator();
                }
            }
        }

        yield Await::ALL;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.for.setting", function (Player $player) {
                $parents = Session::getSession($player)->get("parents");
                $recipe = array_shift($parents);
                $variables = $recipe->getAddingVariablesBefore($this, $parents, FlowItemContainer::ACTION);
                $this->sendSettingCounter($player, $variables);
            }),
        ];
    }

    public function sendSettingCounter(Player $player, array $variables): void {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new PositionVariableDropdown($variables, $this->getPositionVariableName("pos1"), "@action.foreachPosition.form.pos1"),
                new PositionVariableDropdown($variables, $this->getPositionVariableName("pos2"), "@action.foreachPosition.form.pos2"),
                new ExampleInput("@action.for.counterName", "pos", $this->counterName, true),
            ])->onReceive(function (Player $player, array $data) {
                $this->setPositionVariableName($data[0], "pos1");
                $this->setPositionVariableName($data[1], "pos2");
                $this->counterName = $data[2];
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        $this->setPositionVariableName($contents[1], "pos1");
        $this->setPositionVariableName($contents[2], "pos2");
        $this->counterName = $contents[3];
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getActions(),
            $this->getPositionVariableName("pos1"),
            $this->getPositionVariableName("pos2"),
            $this->counterName,
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->counterName => new DummyVariable(PositionVariable::class),
        ];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
