<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Session;
use pocketmine\Player;

class IFAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_IF;

    protected $name = "action.if.name";
    protected $detail = "action.if.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        $this->setItems($conditions, FlowItemContainer::CONDITION);
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["", "==============if================"];
        foreach ($this->getItems(FlowItemContainer::CONDITION) as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->getItems(FlowItemContainer::ACTION) as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin) {
        foreach ($this->getItems(FlowItemContainer::CONDITION) as $condition) {
            if (!(yield from $condition->execute($origin))) return false;
        }

        yield from $this->executeAll($origin, "action");
        return true;
    }

    public function isDataValid(): bool {
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
                new Button("@condition.edit"),
                new Button("@action.edit"),
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
                        (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION);
                        break;
                    case 2:
                        (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION);
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
                $session = Session::getSession($player);
                $session->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $i => $content) {
            switch ($content["id"]) {
                case "removeItem":
                    $content["id"] = self::REMOVE_ITEM_CONDITION;
                    break;
                case "takeMoney":
                    $content["id"] = self::TAKE_MONEY_CONDITION;
                    break;
            }
            $condition = FlowItem::loadSaveDataStatic($content);
            $this->addItem($condition, FlowItemContainer::CONDITION);
        }

        foreach ($contents[1] as $i => $content) {
            $action = FlowItem::loadSaveDataStatic($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->getItems(FlowItemContainer::CONDITION),
            $this->getItems(FlowItemContainer::ACTION)
        ];
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getItems(FlowItemContainer::CONDITION) as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setItems($conditions, FlowItemContainer::CONDITION);

        $actions = [];
        foreach ($this->getItems(FlowItemContainer::ACTION) as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}