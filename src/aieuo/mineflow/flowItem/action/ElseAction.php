<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class ElseAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_ELSE;

    protected $name = "action.else.name";
    protected $detail = "action.else.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /* @var bool */
    private $lastResult;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["==============else=============="];
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
        $lastResult = $this->getParent()->getLastActionResult();
        if ($lastResult === null) throw new InvalidFlowValueException();
        if ($lastResult) return false;

        yield from $this->executeAll($origin, FlowItemContainer::ACTION);
        return true;
    }

    public function getLastActionResult(): ?bool {
        return $this->lastResult;
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
                        (new FlowItemForm)->sendChangeName($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                    case 3:
                        (new FlowItemContainerForm)->sendMoveAction($player, $parent, FlowItemContainer::ACTION, array_search($this, $parent->getActions(), true));
                        break;
                    case 4:
                        (new FlowItemForm)->sendConfirmDelete($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents as $content) {
            $action = FlowItem::loadSaveDataStatic($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->getItems(FlowItemContainer::ACTION);
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getItems(FlowItemContainer::ACTION) as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}