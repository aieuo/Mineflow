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
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\player\Player;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class ForeachPosition extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    private string $counterName = "pos";

    private PositionArgument $position1;
    private PositionArgument $position2;

    public function __construct(string $pos1 = "pos1", string $pos2 = "pos2", array $actions = [], ?string $customName = null) {
        parent::__construct(self::FOREACH_POSITION, FlowItemCategory::SCRIPT_LOOP);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->position1 = new PositionArgument("pos1", $pos1, "@action.foreachPosition.form.pos1");
        $this->position2 = new PositionArgument("pos2", $pos2, "@action.foreachPosition.form.pos2");
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $repeat = $this->position1->get()." -> ".$this->position2->get()."; (".$this->counterName.")";

        $details = ["", "§7==§f eachPos(".$repeat.") §7==§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getShortDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getPosition1(): PositionArgument {
        return $this->position1;
    }

    public function getPosition2(): PositionArgument {
        return $this->position2;
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $counterName = $source->replaceVariables($this->counterName);

        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);

        [$sx, $ex] = [min($pos1->x, $pos2->x), max($pos1->x, $pos2->x)];
        [$sy, $ey] = [min($pos1->y, $pos2->y), max($pos1->y, $pos2->y)];
        [$sz, $ez] = [min($pos1->z, $pos2->z), max($pos1->z, $pos2->z)];

        for ($x = $sx; $x <= $ex; $x++) {
            for ($y = $sy; $y <= $ey; $y++) {
                for ($z = $sz; $z <= $ez; $z++) {
                    $pos = new Position($x, $y, $z, $pos1->getWorld());

                    yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                        $counterName => new PositionVariable($pos)
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
                $this->position1->createFormElement($variables),
                $this->position2->createFormElement($variables),
                new ExampleInput("@action.for.counterName", "pos", $this->counterName, true),
            ])->onReceive(function (Player $player, array $data) {
                $this->position1->set($data[0]);
                $this->position2->set($data[1]);
                $this->counterName = $data[2];
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): void {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        $this->position1->set($contents[1]);
        $this->position2->set($contents[2]);
        $this->counterName = $contents[3];
    }

    public function serializeContents(): array {
        return [
            $this->getActions(),
            $this->position1->get(),
            $this->position2->get(),
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

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
