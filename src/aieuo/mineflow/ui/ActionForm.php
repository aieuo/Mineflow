<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\script\ScriptFactory;
use aieuo\mineflow\action\script\ActionScript;
use aieuo\mineflow\action\process\ProcessFactory;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Button;

class ActionForm {

    public function sendAddedActionMenu(Player $player, ActionContainer $container, Action $action, array $messages = []) {
        (new ListForm(Language::get("form.action.addedActionMenu.title", [$container->getName(), $action->getName()])))
            ->setContent(trim($action->getDetail()))
            ->addButtons([// TODO: 移動させるボタン
                new Button("@form.back"),
                new Button("@form.edit"),
                new Button("@form.delete"),
            ])->onRecive(function (Player $player, ?int $data, ActionContainer $container, Action $action) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $session = Session::getSession($player);
                        $parents = $session->get("parents");
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ActionContainerForm)->sendActionList($player, $container);
                        break;
                    case 1:
                        if ($action instanceof ActionScript) {
                            $session = Session::getSession($player);
                            $session->set("parents", array_merge($session->get("parents"), [$container]));
                            $action->sendEditForm($player);
                            return;
                        }
                        $action->getEditForm()
                            ->addArgs($container, $action, function ($result) use ($player, $container, $action) {
                                $this->sendAddedActionMenu($player, $container, $action, [$result ? "@form.changed" : "@form.cancelled"]);
                            })->onRecive([$this, "onUpdateAction"])->show($player);
                        break;
                    case 2:
                        $this->sendConfirmDelete($player, $action, $container);
                        break;
                }
            })->addArgs($container, $action)->addMessages($messages)->show($player);
    }

    public function onUpdateAction(Player $player, ?array $data, ActionContainer $container, Action $action, callable $callback) {
        if ($data === null) return;

        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            call_user_func_array($callback, [false]);
            return;
        }

        if ($datas["status"] === false) {
            $action->getEditForm($data, $datas["errors"])
                ->addArgs($container, $action, $callback)
                ->onRecive([$this, "onUpdateAction"])
                ->show($player);
            return;
        }
        $action->parseFromSaveData($datas["contents"]);
        call_user_func_array($callback, [true]);
    }

    public function selectActionCategory(Player $player, ActionContainer $container) {
        $buttons = [new Button("@form.back"), new Button("@form.items.category.favorite")];
        $categories = Categories::getActionCategories();
        foreach ($categories as $category) {
            $buttons[] = new Button("@category.".$category);
        }
        (new ListForm(Language::get("form.action.category.title", [$container->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, ActionContainer $container, array $categories) {
                if ($data === null) return;

                if ($data === 0) {
                    (new ActionContainerForm)->sendActionList($player, $container);
                    return;
                }
                if ($data === 1) {
                    $favorites = Main::getInstance()->getFavorites()->getNested($player->getName().".action", []);
                    $actions = [];
                    foreach ($favorites as $favorite) {
                        $action = ProcessFactory::get($favorite);
                        if ($action === null) $action = ScriptFactory::get($favorite);
                        if ($action === null) continue;

                        $actions[] = $action;
                    }
                    $this->sendSelectAction($player, $container, $actions, Language::get("form.items.category.favorite"));
                    return;
                }
                $data -= 2;

                $category = $categories[$data];
                $actions = ProcessFactory::getByCategory($category);
                $actions = array_merge($actions, ScriptFactory::getByCategory($category));

                $this->sendSelectAction($player, $container, $actions, Categories::getActionCategories()[$category]);
            })->addArgs($container, array_keys($categories))->show($player);
    }

    public function sendSelectAction(Player $player, ActionContainer $container, array $actions, string $category = "") {
        $buttons = [new Button("@form.back")];
        foreach ($actions as $action) {
            $buttons[] = new Button($action->getName());
        }
        (new ListForm(Language::get("form.action.select.title", [$container->getName(), $category])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, ActionContainer $container, array $actions) {
                if ($data === null) return;

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
        $config = Main::getInstance()->getFavorites();
        $favorites = $config->getNested($player->getName().".action", []);
        (new ListForm(Language::get("form.action.menu.title", [$container->getName(), $action->getName()])))
            ->setContent($action->getDescription())
            ->addButtons([
                new Button("@form.add"),
                new Button(in_array($action->getId(), $favorites) ? "@form.items.removeFavorite" : "@form.items.addFavorite"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, ActionContainer $container, Action $action) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $session = Session::getSession($player);
                        $session->set("parents", array_merge($session->get("parents"), [$container]));
                        if ($action instanceof ActionScript) {
                            $container->addAction($action);
                            $action->sendEditForm($player);
                            return;
                        }
                        $action->getEditForm()
                            ->addArgs($container, $action, function ($result) use ($player, $container, $action) {
                                if ($result) {
                                    $container->addAction($action);
                                    $this->sendAddedActionMenu($player, $container, $action, ["@form.changed"]);
                                } else {
                                    $this->sendActionMenu($player, $container, $action, ["@form.cancelled"]);
                                }
                            })->onRecive([$this, "onUpdateAction"])->show($player);
                        break;
                    case 1:
                        $config = Main::getInstance()->getFavorites();
                        $favorites = $config->getNested($player->getName().".action", []);
                        if (in_array($action->getId(), $favorites)) {
                            $favorites = array_diff($favorites, [$action->getId()]);
                            $favorites = array_values($favorites);
                        } else {
                            $favorites[] = $action->getId();
                        }
                        $config->setNested($player->getName().".action", $favorites);
                        $config->save();
                        $this->sendActionMenu($player, $container, $action, ["@form.changed"]);
                        break;
                    default:
                        $actions = Session::getSession($player)->get("actions");
                        $this->sendSelectAction($player, $container, $actions);
                        break;
                }
            })->addArgs($container, $action)->addMessages($messages)->show($player);
    }

    public function sendConfirmDelete(Player $player, Action $action, ActionContainer $container) {
        (new ModalForm(Language::get("form.items.delete.title", [$container->getName(), $action->getName()])))
            ->setContent(Language::get("form.delete.confirm", [trim($action->getDetail())]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, Action $action, ActionContainer $container) {
                if ($data === null) return;

                if ($data) {
                    $index = array_search($action, $container->getActions());
                    $container->removeAction($index);
                    $session = Session::getSession($player);
                    $parents = $session->get("parents");
                    array_pop($parents);
                    $session->set("parents", $parents);
                    (new ActionContainerForm)->sendActionList($player, $container, ["@form.delete.success"]);
                } elseif ($container instanceof ActionScript) {
                    $container->sendEditForm($player, false, ["@form.cancelled"]);
                } else {
                    $this->sendAddedActionMenu($player, $container, $action, ["@form.cancelled"]);
                }
            })->addArgs($action, $container)->show($player);
    }
}