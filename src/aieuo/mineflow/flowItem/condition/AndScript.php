<?php

namespace aieuo\mineflow\flowItem\condition;

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

class AndScript extends FlowItem implements Condition, FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::CONDITION_AND;

    protected $name = "condition.and.name";
    protected $detail = "condition.and.detail";

    protected $category = Category::SCRIPT;

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin) {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($origin))) return false;
        }
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
                        (new FlowItemContainerForm)->sendActionList($player, $parent, FlowItemContainer::CONDITION);
                        break;
                    case 1:
                        (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION);
                        break;
                    case 2:
                        (new FlowItemForm)->sendChangeName($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                    case 3:
                        (new FlowItemContainerForm)->sendMoveAction($player, $parent, FlowItemContainer::CONDITION, array_search($this, $parent->getConditions(), true));
                        break;
                    case 4:
                        (new FlowItemForm)->sendConfirmDelete($player, $this, $parent, FlowItemContainer::CONDITION);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents as $content) {
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
        return $this;
    }

    public function serializeContents(): array {
        return $this->getConditions();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getConditions() as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setItems($conditions, FlowItemContainer::CONDITION);
    }
}