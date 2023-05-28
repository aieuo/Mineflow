<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\FlowItemMenuButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_search;
use function array_shift;

class FlowItemForm {

    public function sendAddedItemMenu(Player $player, FlowItemContainer $parent, string $type, FlowItem $item, array $messages = []): void {
        if ($item->hasCustomMenu()) {
            $this->sendFlowItemCustomMenu($player, $item, $type);
            return;
        }

        /** @var Recipe|FlowItem $parent */
        (new ListForm(Language::get("form.$type.addedItemMenu.title", [$parent->getContainerName(), $item->getName()])))
            ->setContent(trim($item->getCustomName()."\n\n".ltrim($item->getDetail())))
            ->addButtons([
                new FlowItemMenuButton("@form.back", $item, $parent, $type, [$this, "onSelectBack"]),
                new FlowItemMenuButton("@form.edit", $item, $parent, $type, [$this, "onSelectEdit"]),
                new FlowItemMenuButton("@form.home.rename.title", $item, $parent, $type, [$this, "onSelectChangeName"]),
                new FlowItemMenuButton("@form.move", $item, $parent, $type, [$this, "onSelectMove"]),
                new FlowItemMenuButton("@form.duplicate", $item, $parent, $type, [$this, "onSelectDuplicate"]),
                new FlowItemMenuButton("@form.delete", $item, $parent, $type, [$this, "onSelectDelete"]),
            ])->addMessages($messages)->show($player);
    }

    public function onSelectBack(Player $player, FlowItemContainer $parent, string $type): void {
        Session::getSession($player)->pop("parents");
        (new FlowItemContainerForm)->sendActionList($player, $parent, $type);
    }

    public function onSelectMove(Player $player, FlowItemContainer $parent, string $type, FlowItem $item): void {
        (new FlowItemContainerForm)->sendMoveAction($player, $parent, $type, array_search($item, $parent->getItems($type), true));
    }

    public function onSelectEdit(Player $player, FlowItemContainer $parent, string $type, FlowItem $item): void {
        Await::f2c(function () use($player, $parent, $type, $item) {
            $parents = Session::getSession($player)->get("parents");
            $recipe = array_shift($parents);
            $variables = $recipe->getAddingVariablesBefore($item, $parents, $type);
            $result = yield from $item->edit($player, $variables, false);
            if ($result === FlowItem::EDIT_CLOSE) return;

            $this->sendAddedItemMenu($player, $parent, $type, $item, [$result === FlowItem::EDIT_SUCCESS ? "@form.changed" : "@form.cancelled"]);
        });
    }

    public function onSelectDuplicate(Player $player, FlowItemContainer $parent, string $type, FlowItem $item): void {
        $newItem = clone $item;
        $parent->addItem($newItem, $type);
        Session::getSession($player)->pop("parents");
        (new FlowItemContainerForm)->sendActionList($player, $parent, $type, ["@form.duplicate.success"]);
    }

    public function onSelectDelete(Player $player, FlowItemContainer $parent, string $type, FlowItem $item): void {
        $this->sendConfirmDelete($player, $item, $parent, $type);
    }

    public function onSelectChangeName(Player $player, FlowItemContainer $parent, string $type, FlowItem $item): void {
        $this->sendChangeName($player, $item, $parent, $type);
    }

    public function sendFlowItemCustomMenu(Player $player, FlowItem $item, string $type, array $messages = []): void {
        $session = Session::getSession($player);
        $parents = $session->get("parents");
        /** @var FlowItemContainer $parent */
        $parent = end($parents);

        /** @var FlowItem|FlowItemContainer $item */
        $detail = trim($item->getCustomName()."\n\n".ltrim($item->getDetail()));
        (new ListForm($item->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButton(new FlowItemMenuButton("@form.back", $item, $parent, $type, [$this, "onSelectBack"]))
            ->addButtons($item->getCustomMenuButtons())
            ->addButton(new FlowItemMenuButton("@form.home.rename.title", $item, $parent, $type, [$this, "onSelectChangeName"]))
            ->addButton(new FlowItemMenuButton("@form.move", $item, $parent, $type, [$this, "onSelectMove"]))
            ->addButton(new FlowItemMenuButton("@form.duplicate", $item, $parent, $type, [$this, "onSelectDuplicate"]))
            ->addButton(new FlowItemMenuButton("@form.delete", $item, $parent, $type, [$this, "onSelectDelete"]))
            ->addMessages($messages)
            ->show($player);
    }

    public function selectActionCategory(Player $player, FlowItemContainer $container, string $type): void {
        $buttons = [
            new Button("@form.back", function () use($player, $container, $type) {
                Session::getSession($player)->pop("parents");
                (new FlowItemContainerForm)->sendActionList($player, $container, $type);
            }),
            new Button("@form.items.category.favorite", function () use($player, $container, $type) {
                $favorites = Mineflow::getPlayerSettings()->getFavorites($player->getName(), $type);
                $actions = [];
                foreach ($favorites as $favorite) {
                    $action = FlowItemFactory::get($favorite);
                    if ($action === null) continue;

                    $actions[] = $action;
                }
                Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.favorite"));
                $this->sendSelectAction($player, $container, $type, $actions);
            })
        ];

        foreach (FlowItemCategory::root() as $category) {
            $buttons[] = $this->getCategoryButton($player, $category, FlowItemCategory::name($category), $container, $type);
        }

        $buttons[] = new Button("@form.search", fn() => $this->sendSearchAction($player, $container, $type));

        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.$type.category.title", [$container->getContainerName()])))
            ->addButtons($buttons)
            ->show($player);
    }

    private function getCategoryButton(Player $player, string $category, string $text, FlowItemContainer $container, string $type): Button {
        return new Button($text, function () use($player, $container, $type, $category) {
            $this->onSelectCategory($player, $category, $container, $type);
        });
    }

    private function onSelectCategory(Player $player, string $category, FlowItemContainer $container, string $type): void {
        $isCondition = $type === FlowItemContainer::CONDITION;
        $actions = FlowItemFactory::getByFilter($category, Mineflow::getPlayerSettings()->getPlayerActionPermissions($player->getName()), !$isCondition, $isCondition);

        Session::getSession($player)->set("flowItem_category", FlowItemCategory::name($category));
        $this->sendSelectAction($player, $container, $type, $actions, $category);
    }

    public function sendSearchAction(Player $player, FlowItemContainer $container, string $type): void {
        (new CustomForm(Language::get("form.{$type}.search.title", [$container->getContainerName()])))
            ->setContents([
                new Input("@form.items.search.keyword", "", Session::getSession($player)->get("flowItem_search", ""), true),
                new CancelToggle(fn() => $this->selectActionCategory($player, $container, $type))
            ])->onReceive(function (Player  $player, array $data) use($container, $type) {
                $isCondition = $type === FlowItemContainer::CONDITION;
                $permissions = Mineflow::getPlayerSettings()->getPlayerActionPermissions($player->getName());
                $actions = FlowItemFactory::getByFilter(null, $permissions, !$isCondition, $isCondition);
                $actions = array_values(array_filter($actions, fn(FlowItem $item) => stripos($item->getName(), $data[0]) !== false));

                Session::getSession($player)->set("flowItem_search", $data[0]);
                Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.search", [$data[0]]));
                $this->sendSelectAction($player, $container, $type, $actions);
            })->show($player);
    }

    public function sendSelectAction(Player $player, FlowItemContainer $container, string $type, array $items, string $category = null): void {
        $buttons = [
            new Button("@form.back", function() use($player, $container, $type, $category) {
                if ($category !== null and ($parent = FlowItemCategory::getParent($category)) !== null) {
                    $this->onSelectCategory($player, $parent, $container, $type);
                } else {
                    $this->selectActionCategory($player, $container, $type);
                }
            })
        ];
        $subCategoryCount = 0;
        if ($category !== null) {
            foreach (FlowItemCategory::getChildren($category) as $child) {
                $buttons[] = $this->getCategoryButton($player, $child, "[".FlowItemCategory::name($child)."]", $container, $type);
                $subCategoryCount ++;
            }
        }
        foreach ($items as $item) {
            $buttons[] = new Button($item->getName());
        }
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.$type.select.title", [$container->getContainerName(), $category ?? ""])))
            ->setContent(count($buttons) === 1 ? "@form.action.empty" : "@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data) use($container, $type, $items, $subCategoryCount) {
                $data --;
                $data -= $subCategoryCount;

                Session::getSession($player)->set($type."s", $items);
                $item = clone $items[$data];
                $this->sendActionMenu($player, $container, $type, $item);
            })->show($player);
    }

    public function sendActionMenu(Player $player, FlowItemContainer $container, string $type, FlowItem $item, array $messages = []): void {
        $favorites = Mineflow::getPlayerSettings()->getFavorites($player->getName(), $type);

        /** @var FlowItemContainer|FlowItem $container */
        (new ListForm(Language::get("form.$type.menu.title", [$container->getContainerName(), $item->getId()])))
            ->setContent($item->getDescription())
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
                new Button(in_array($item->getId(), $favorites, true) ? "@form.items.removeFavorite" : "@form.items.addFavorite"),
            ])->onReceive(function (Player $player, int $data) use($container, $type, $item) {
                switch ($data) {
                    case 0:
                        $actions = Session::getSession($player)->get($type."s");
                        $this->sendSelectAction($player, $container, $type, $actions, $item->getCategory());
                        break;
                    case 1:
                        if ($item->hasCustomMenu()) {
                            $container->addItem($item, $type);
                            $this->sendFlowItemCustomMenu($player, $item, $type);
                            return;
                        }

                        Await::f2c(function () use($player, $container, $type, $item) {
                            $parents = Session::getSession($player)->get("parents");
                            $recipe = array_shift($parents);
                            $variables = $recipe->getAddingVariablesBefore($item, $parents, $type);
                            $result = yield from $item->edit($player, $variables, true);
                            if ($result === FlowItem::EDIT_CLOSE) return;

                            if ($result === FlowItem::EDIT_SUCCESS) {
                                $container->addItem($item, $type);
                                Session::getSession($player)->pop("parents");
                                (new FlowItemContainerForm)->sendActionList($player, $container, $type, ["@form.added"]);
                            } else {
                                $this->sendActionMenu($player, $container, $type, $item, ["@form.cancelled"]);
                            }
                        });
                        break;
                    case 2:
                        $config = Mineflow::getPlayerSettings();
                        $config->toggleFavorite($player->getName(), $type, $item->getId());
                        $config->save();
                        $this->sendActionMenu($player, $container, $type, $item, ["@form.changed"]);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendConfirmDelete(Player $player, FlowItem $action, FlowItemContainer $container, string $type): void {
        (new ModalForm(Language::get("form.items.delete.title", [$container->getContainerName(), $action->getName()])))
            ->setContent(Language::get("form.delete.confirm", [trim($action->getDetail())]))
            ->onYes(function() use ($player, $action, $container, $type) {
                $index = array_search($action, $container->getItems($type), true);
                $container->removeItem($index, $type);
                Session::getSession($player)->pop("parents");
                (new FlowItemContainerForm)->sendActionList($player, $container, $type, ["@form.deleted"]);
            })->onNo(function() use ($player, $action, $container, $type) {
                if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                    $this->sendFlowItemCustomMenu($player, $container, $type, ["@form.cancelled"]);
                } else {
                    $this->sendAddedItemMenu($player, $container, $type, $action, ["@form.cancelled"]);
                }
            })->show($player);
    }

    public function sendChangeName(Player $player, FlowItem $item, FlowItemContainer $container, string $type): void {
        (new CustomForm(Language::get("form.recipe.changeName.title", [$item->getName()])))
            ->setContents([
                new Input("@form.recipe.changeName.content1", "", $item->getCustomName()),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) use($item, $container, $type) {
                if ($data[1]) {
                    if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                        $this->sendFlowItemCustomMenu($player, $container, $type, ["@form.cancelled"]);
                    } else {
                        (new FlowItemForm)->sendAddedItemMenu($player, $container, $type, $item, ["@form.cancelled"]);
                    }
                    return;
                }

                $item->setCustomName($data[0]);
                if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                    $this->sendFlowItemCustomMenu($player, $container, $type, ["@form.changed"]);
                } else {
                    (new FlowItemForm)->sendAddedItemMenu($player, $container, $type, $item, ["@form.changed"]);
                }
            })->addArgs($item, $container)->show($player);
    }
}
