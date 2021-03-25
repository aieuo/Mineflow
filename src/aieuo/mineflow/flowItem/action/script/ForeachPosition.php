<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;
use pocketmine\Player;

class ForeachPosition extends FlowItem implements FlowItemContainer, PositionFlowItem {
    use FlowItemContainerTrait, PositionFlowItemTrait;

    protected $id = self::FOREACH_POSITION;

    protected $name = "action.foreachPosition.name";
    protected $detail = "action.foreachPosition.description";

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_1;

    /* @var array */
    private $counter;
    /* @var string */
    private $counterName = "pos";

    public function __construct(string $pos1 = "pos1", string $pos2 = "pos2", array $actions = [], ?string $customName = null) {
        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->setCustomName($customName);
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

    public function execute(FlowItemExecutor $source): \Generator {
        $counterName = $source->replaceVariables($this->counterName);

        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");

        [$sx, $ex] = [min($pos1->x, $pos2->x), max($pos1->x, $pos2->x)];
        [$sy, $ey] = [min($pos1->y, $pos2->y), max($pos1->y, $pos2->y)];
        [$sz, $ez] = [min($pos1->z, $pos2->z), max($pos1->z, $pos2->z)];

        for ($x = $sx; $x <= $ex; $x++) {
            for ($y = $sy; $y <= $ey; $y++) {
                for ($z = $sz; $z <= $ez; $z++) {
                    $pos = new Position($x, $y, $z, $pos1->getLevel());

                    yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                        $counterName => new PositionObjectVariable($pos, $counterName)
                    ], $source))->executeGenerator();
                }
            }
        }

        $source->resume();
        yield true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function sendCustomMenu(Player $player, array $messages = []): void {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@action.edit"),
                new Button("@action.for.setting"),
                new Button("@form.home.rename.title"),
                new Button("@form.move"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                $parents = $session->get("parents");
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        $session->pop("parents");
                        (new FlowItemContainerForm)->sendActionList($player, $parent, FlowItemContainer::ACTION);
                        break;
                    case 1:
                        (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION);
                        break;
                    case 2:
                        $this->sendSettingCounter($player);
                        break;
                    case 3:
                        (new FlowItemForm)->sendChangeName($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                    case 4:
                        (new FlowItemContainerForm)->sendMoveAction($player, $parent, FlowItemContainer::ACTION, array_search($this, $parent->getActions(), true));
                        break;
                    case 5:
                        (new FlowItemForm)->sendConfirmDelete($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function sendSettingCounter(Player $player): void {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.foreachPosition.form.pos1", "pos1", $this->getPositionVariableName("pos1"), true),
                new ExampleInput("@action.foreachPosition.form.pos2", "pos2", $this->getPositionVariableName("pos2"), true),
                new ExampleInput("@action.for.counterName", "pos", $this->counterName, true),
            ])->onReceive(function (Player $player, array $data) {
                $this->setPositionVariableName($data[0], "pos1");
                $this->setPositionVariableName($data[1], "pos2");
                $this->counterName = $data[2];
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
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
            $this->counterName => new DummyVariable(DummyVariable::POSITION),
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
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}