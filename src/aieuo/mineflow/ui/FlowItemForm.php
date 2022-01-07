<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;

class FlowItemForm {

    public function sendAddedItemMenu(Player $player, FlowItemContainer $container, string $type, FlowItem $action, array $messages = []): void {
        if ($action->hasCustomMenu()) {
            $this->sendFlowItemCustomMenu($player, $action, $type);
            return;
        }

        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.$type.addedItemMenu.title", [$container->getContainerName(), $action->getName()])))
            ->setContent(trim($action->getDetail()))
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.edit"),
                new Button("@form.move"),
                new Button("@form.duplicate"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data) use($container, $type, $action) {
                switch ($data) {
                    case 0:
                        Session::getSession($player)->pop("parents");
                        (new FlowItemContainerForm)->sendActionList($player, $container, $type);
                        break;
                    case 1:
                        $parents = Session::getSession($player)->get("parents");
                        $recipe = array_shift($parents);
                        $variables = $recipe->getAddingVariablesBefore($action, $parents, $type);
                        $form = $action->getEditForm($variables);
                        $form->addArgs($form, $action, function ($result) use ($player, $container, $type, $action) {
                            $this->sendAddedItemMenu($player, $container, $type, $action, [$result ? "@form.changed" : "@form.cancelled"]);
                        })->onReceive([$this, "onUpdateAction"])->show($player);
                        break;
                    case 2:
                        (new FlowItemContainerForm)->sendMoveAction($player, $container, $type, array_search($action, $container->getItems($type), true));
                        break;
                    case 3:
                        $newItem = clone $action;
                        $container->addItem($newItem, $type);
                        Session::getSession($player)->pop("parents");
                        (new FlowItemContainerForm)->sendActionList($player, $container, $type, ["@form.duplicate.success"]);
                        break;
                    case 4:
                        $this->sendConfirmDelete($player, $action, $container, $type);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendFlowItemCustomMenu(Player $player, FlowItem $action, string $type, array $messages = []): void {
        $session = Session::getSession($player);
        $parents = $session->get("parents");
        /** @var FlowItemContainer $parent */
        $parent = end($parents);

        /** @var FlowItem|FlowItemContainer $action */
        $detail = trim($action->getDetail());
        (new ListForm($action->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButton(
                new Button("@form.back", function () use($player, $session, $parent, $type) {
                    $session->pop("parents");
                    (new FlowItemContainerForm)->sendActionList($player, $parent, $type);
                }))
            ->addButtons($action->getCustomMenuButtons())
            ->addButton(new Button("@form.home.rename.title", fn() => $this->sendChangeName($player, $action, $parent, $type)))
            ->addButton(new Button("@form.move", fn() => (new FlowItemContainerForm)->sendMoveAction($player, $parent, $type, array_search($action, $parent->getActions(), true))))
            ->addButton(
                new Button("@form.duplicate", function () use($player, $action, $parent, $type) {
                    $newItem = clone $action;
                    $parent->addItem($newItem, $type);
                    Session::getSession($player)->pop("parents");
                    (new FlowItemContainerForm)->sendActionList($player, $parent, $type, ["@form.duplicate.success"]);
                }))
            ->addButton(new Button("@form.delete", fn() => $this->sendConfirmDelete($player, $action, $parent, $type)))
            ->addMessages($messages)
            ->show($player);
    }

    public function onUpdateAction(Player $player, ?array $data, Form $form, FlowItem $action, callable $callback): void {
        if ($data === null) return;

        array_shift($data);
        $cancelChecked = array_pop($data);

        if ($cancelChecked) {
            $callback(false);
            return;
        }

        try {
            $values = $action->parseFromFormData($data);
        } catch (InvalidFormValueException $e) {
            $form->resend([[$e->getMessage(), $e->getIndex() + 1]]);
            return;
        }

        try {
            $action->loadSaveData($values);
        } catch (FlowItemLoadException|\ErrorException $e) {
            $player->sendMessage(Language::get("action.error.recipe"));
            Main::getInstance()->getLogger()->logException($e);
            return;
        }
        $callback(true);
    }

    public function selectActionCategory(Player $player, FlowItemContainer $container, string $type): void {
        $buttons = [
            new Button("@form.back", function () use($player, $container, $type) {
                Session::getSession($player)->pop("parents");
                (new FlowItemContainerForm)->sendActionList($player, $container, $type);
            }),
            new Button("@form.items.category.favorite", function () use($player, $container, $type) {
                $favorites = Main::getInstance()->getPlayerSettings()->getFavorites($player->getName(), $type);
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

        foreach (FlowItemCategory::all() as $category) {
            $buttons[] = new Button("@category.".$category, function () use($player, $container, $type, $category) {
                $isCondition = $type === FlowItemContainer::CONDITION;
                $actions = FlowItemFactory::getByFilter($category, Main::getInstance()->getPlayerSettings()->getPlayerActionPermissions($player->getName()), !$isCondition, $isCondition);

                Session::getSession($player)->set("flowItem_category", Language::get("category.".$category));
                $this->sendSelectAction($player, $container, $type, $actions);
            });
        }

        $buttons[] = new Button("@form.search", fn() => $this->sendSearchAction($player, $container, $type));

        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.$type.category.title", [$container->getContainerName()])))
            ->addButtons($buttons)
            ->show($player);
    }

    public function sendSearchAction(Player $player, FlowItemContainer $container, string $type): void {
        (new CustomForm(Language::get("form.{$type}.search.title", [$container->getContainerName()])))
            ->setContents([
                new Input("@form.items.search.keyword", "", Session::getSession($player)->get("flowItem_search", ""), true),
                new CancelToggle(fn() => $this->selectActionCategory($player, $container, $type))
            ])->onReceive(function (Player  $player, array $data) use($container, $type) {
                $isCondition = $type === FlowItemContainer::CONDITION;
                $permissions = Main::getInstance()->getPlayerSettings()->getPlayerActionPermissions($player->getName());
                $actions = FlowItemFactory::getByFilter(null, $permissions, !$isCondition, $isCondition);
                $actions = array_values(array_filter($actions, fn(FlowItem $item) => stripos($item->getName(), $data[0]) !== false));

                Session::getSession($player)->set("flowItem_search", $data[0]);
                Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.search", [$data[0]]));
                $this->sendSelectAction($player, $container, $type, $actions);
            })->show($player);
    }

    public function sendSelectAction(Player $player, FlowItemContainer $container, string $type, array $items): void {
        $buttons = [
            new Button("@form.back", fn() => $this->selectActionCategory($player, $container, $type))
        ];
        foreach ($items as $item) {
            $buttons[] = new Button($item->getName());
        }
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.$type.select.title", [$container->getContainerName(), Session::getSession($player)->get("flowItem_category", "")])))
            ->setContent(count($buttons) === 1 ? "@form.action.empty" : "@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data) use($container, $type, $items) {
                $data --;

                Session::getSession($player)->set($type."s", $items);
                $item = clone $items[$data];
                $this->sendActionMenu($player, $container, $type, $item);
            })->show($player);
    }

    public function sendActionMenu(Player $player, FlowItemContainer $container, string $type, FlowItem $item, array $messages = []): void {
        $favorites = Main::getInstance()->getPlayerSettings()->getFavorites($player->getName(), $type);

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
                        $this->sendSelectAction($player, $container, $type, $actions);
                        break;
                    case 1:
                        if ($item->hasCustomMenu()) {
                            $container->addItem($item, $type);
                            $this->sendFlowItemCustomMenu($player, $item, $type);
                            return;
                        }

                        $parents = Session::getSession($player)->get("parents");
                        $recipe = array_shift($parents);
                        $variables = $recipe->getAddingVariablesBefore($item, $parents, $type);
                        $form = $item->getEditForm($variables);
                        $form->addArgs($form, $item, function ($result) use ($player, $container, $type, $item) {
                            if ($result) {
                                $container->addItem($item, $type);
                                Session::getSession($player)->pop("parents");
                                (new FlowItemContainerForm)->sendActionList($player, $container, $type, ["@form.added"]);
                            } else {
                                $this->sendActionMenu($player, $container, $type, $item, ["@form.cancelled"]);
                            }
                        })->onReceive([new FlowItemForm(), "onUpdateAction"])->show($player);
                        break;
                    case 2:
                        $config = Main::getInstance()->getPlayerSettings();
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