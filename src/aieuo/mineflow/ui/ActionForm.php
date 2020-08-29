<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\action\ActionContainer;
use aieuo\mineflow\flowItem\action\Action;
use aieuo\mineflow\flowItem\action\ActionFactory;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Button;

class ActionForm {

    public function sendAddedActionMenu(Player $player, ActionContainer $container, Action $action, array $messages = []) {
        if ($action->hasCustomMenu()) {
            $action->sendCustomMenu($player);
            return;
        }
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.action.addedActionMenu.title", [$container->getContainerName(), $action->getName()])))
            ->setContent(trim($action->getDetail()))
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.edit"),
                new Button("@form.move"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, ActionContainer $container, Action $action) {
                switch ($data) {
                    case 0:
                        Session::getSession($player)->pop("parents");
                        (new ActionContainerForm)->sendActionList($player, $container);
                        break;
                    case 1:
                        $action->getEditForm()
                            ->addArgs($container, $action, function ($result) use ($player, $container, $action) {
                                $this->sendAddedActionMenu($player, $container, $action, [$result ? "@form.changed" : "@form.cancelled"]);
                            })->onReceive([$this, "onUpdateAction"])->show($player);
                        break;
                    case 2:
                        (new ActionContainerForm)->sendMoveAction($player, $container, array_search($action, $container->getActions(), true));
                        break;
                    case 3:
                        $this->sendConfirmDelete($player, $action, $container);
                        break;
                }
            })->addArgs($container, $action)->addMessages($messages)->show($player);
    }

    public function onUpdateAction(Player $player, ?array $formData, ActionContainer $container, Action $action, callable $callback) {
        if ($formData === null) return;

        $data = $action->parseFromFormData($formData);
        if ($data["cancel"]) {
            call_user_func_array($callback, [false]);
            return;
        }

        if (!empty($data["errors"])) {
            $action->getEditForm($formData, $data["errors"])
                ->addArgs($container, $action, $callback)
                ->onReceive([$this, "onUpdateAction"])
                ->show($player);
            return;
        }
        try {
            $action->loadSaveData($data["contents"]);
        } catch (\ErrorException $e) {
            $player->sendMessage(Language::get("action.error.recipe"));
            Main::getInstance()->getLogger()->logException($e);
            return;
        }
        call_user_func_array($callback, [true]);
    }

    public function selectActionCategory(Player $player, ActionContainer $container) {
        $buttons = [new Button("@form.back"), new Button("@form.items.category.favorite")];
        $categories = Category::getCategories();
        foreach ($categories as $category) {
            $buttons[] = new Button("@category.".$category);
        }
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.action.category.title", [$container->getContainerName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, ActionContainer $container, array $categories) {
                if ($data === 0) {
                    (new ActionContainerForm)->sendActionList($player, $container);
                    return;
                }
                if ($data === 1) {
                    $favorites = Main::getInstance()->getPlayerSettings()->getFavorites($player->getName(), "action");
                    $actions = [];
                    foreach ($favorites as $favorite) {
                        $action = ActionFactory::get($favorite);
                        if ($action === null) continue;

                        $actions[] = $action;
                    }
                    Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.favorite"));
                    $this->sendSelectAction($player, $container, $actions);
                    return;
                }
                $data -= 2;

                $category = $categories[$data];
                $actions = ActionFactory::getByFilter($category, Main::getInstance()->getPlayerSettings()->getNested($player->getName().".permission", 0));

                Session::getSession($player)->set("flowItem_category", Language::get("category.".$category));
                $this->sendSelectAction($player, $container, $actions);
            })->addArgs($container, $categories)->show($player);
    }

    public function sendSelectAction(Player $player, ActionContainer $container, array $actions) {
        $buttons = [new Button("@form.back")];
        foreach ($actions as $action) {
            $buttons[] = new Button($action->getName());
        }
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.action.select.title", [$container->getContainerName(), Session::getSession($player)->get("flowItem_category", "")])))
            ->setContent(count($buttons) === 1 ? "@form.flowItem.empty" : "@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, ActionContainer $container, array $actions) {
                if ($data === 0) {
                    $this->selectActionCategory($player, $container);
                    return;
                }
                $data -= 1;

                Session::getSession($player)->set("actions", $actions);
                $action = clone $actions[$data];
                $this->sendActionMenu($player, $container, $action);
            })->addArgs($container, $actions)->show($player);
    }

    public function sendActionMenu(Player $player, ActionContainer $container, Action $action, array $messages = []) {
        $favorites = Main::getInstance()->getPlayerSettings()->getFavorites($player->getName(), "action");
        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.action.menu.title", [$container->getContainerName(), $action->getId()])))
            ->setContent($action->getDescription()."\n"./*TODO: いる...?*/Language::get("flowItem.target.require", [["flowItem.target.require.".$action->getRequiredTarget()]]))
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
                new Button(in_array($action->getId(), $favorites) ? "@form.items.removeFavorite" : "@form.items.addFavorite"),
            ])->onReceive(function (Player $player, int $data, ActionContainer $container, Action $action) {
                switch ($data) {
                    case 0:
                        $actions = Session::getSession($player)->get("actions");
                        $this->sendSelectAction($player, $container, $actions);
                        break;
                    case 1:
                        if ($action->hasCustomMenu()) {
                            Session::getSession($player)->push("parents", $container);
                            $container->addAction($action);
                            $action->sendCustomMenu($player);
                            return;
                        }
                        $action->getEditForm()
                            ->addArgs($container, $action, function ($result) use ($player, $container, $action) {
                                if ($result) {
                                    $container->addAction($action);
                                    (new ActionContainerForm)->sendActionList($player, $container, ["@form.added"]);
                                } else {
                                    $this->sendActionMenu($player, $container, $action, ["@form.cancelled"]);
                                }
                            })->onReceive([$this, "onUpdateAction"])->show($player);
                        break;
                    case 2:
                        $config = Main::getInstance()->getPlayerSettings();
                        $config->toggleFavorite($player->getName(), "action", $action->getId());
                        $config->save();
                        $this->sendActionMenu($player, $container, $action, ["@form.changed"]);
                        break;
                }
            })->addArgs($container, $action)->addMessages($messages)->show($player);
    }

    /**
     * @param Player $player
     * @param Action $action
     * @param ActionContainer $container
     * @uses \aieuo\mineflow\flowItem\action\ActionContainerTrait::removeAction()
     */
    public function sendConfirmDelete(Player $player, Action $action, ActionContainer $container) {
        (new MineflowForm)->confirmDelete($player,
            Language::get("form.items.delete.title", [$container->getContainerName(), $action->getName()]), trim($action->getDetail()),
            function (Player $player) use ($action, $container) {
                $index = array_search($action, $container->getActions(), true);
                $container->removeAction($index);
                Session::getSession($player)->pop("parents");
                (new ActionContainerForm)->sendActionList($player, $container, ["@form.delete.success"]);
            },
            function (Player $player) use ($action, $container) {
                if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                    $container->sendCustomMenu($player, ["@form.cancelled"]);
                } else {
                    $this->sendAddedActionMenu($player, $container, $action, ["@form.cancelled"]);
                }
            });
    }
}