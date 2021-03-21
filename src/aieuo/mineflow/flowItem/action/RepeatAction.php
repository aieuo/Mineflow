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
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\Player;

class RepeatAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_REPEAT;

    protected $name = "action.repeat.name";
    protected $detail = "action.repeat.description";

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $repeatCount;

    /** @var string */
    private $startIndex = "0";
    /** @var string */
    private $counterName = "i";

    public function __construct(array $actions = [], int $count = 1, ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->repeatCount = (string)$count;
        $this->setCustomName($customName);
    }

    public function setRepeatCount(string $count): void {
        $this->repeatCount = $count;
    }

    public function getRepeatCount(): string {
        return $this->repeatCount;
    }

    public function setStartIndex(string $startIndex): self {
        $this->startIndex = $startIndex;
        return $this;
    }

    public function getStartIndex(): string {
        return $this->startIndex;
    }

    public function setCounterName(string $counterName): self {
        $this->counterName = $counterName;
        return $this;
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
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $count = $source->replaceVariables($this->repeatCount);
        $this->throwIfInvalidNumber($count, 1);

        $start = $source->replaceVariables($this->startIndex);
        $this->throwIfInvalidNumber($start);

        $name = $this->counterName;
        $end = (int)$start + (int)$count;

        for ($i = (int)$start; $i < $end; $i++) {
            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                $name => new NumberVariable($i, $name)
            ], $source))->executeGenerator();
        }
        $source->resume();
        return true;
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
                new Button("@action.repeat.editCount"),
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
                        $this->sendSetRepeatCountForm($player);
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

    public function sendSetRepeatCountForm(Player $player): void {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new ExampleNumberInput("@action.repeat.repeatCount", "10", $this->getRepeatCount(), true, 1),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    $this->sendCustomMenu($player, ["@form.cancelled"]);
                    return;
                }

                $this->setRepeatCount($data[0]);
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        $this->setRepeatCount((string)$contents[0]);

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }

        if (isset($contents[2])) $this->startIndex = (string)$contents[2];
        if (isset($contents[3])) $this->counterName = $contents[3];
        return $this;
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
        return [new DummyVariable($this->getCounterName(), DummyVariable::NUMBER)];
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