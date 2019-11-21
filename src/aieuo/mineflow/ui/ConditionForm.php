<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\script\ScriptFactory;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\condition\script\ConditionScript;
use aieuo\mineflow\condition\Conditionable;
use aieuo\mineflow\condition\ConditionFactory;
use aieuo\mineflow\condition\ConditionContainer;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Button;

class ConditionForm {

    public function sendAddedConditionMenu(Player $player, ConditionContainer $container, Conditionable $condition, array $messages = []) {
        (new ListForm(Language::get("form.condition.addedConditionMenu.title", [$container->getName(), $condition->getName()])))
            ->setContent(trim($condition->getDetail()))
            ->addButtons([// TODO: 移動させるボタン
                new Button("@form.edit"),
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, ConditionContainer $container, Conditionable $condition) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        if ($condition instanceof ConditionScript) {
                            $session = Session::getSession($player);
                            $session->set("parents", array_merge($session->get("parents"), [$container]));
                            $condition->sendEditForm($player);
                            return;
                        }
                        $condition->getEditForm()
                            ->addArgs($container, $condition, function ($result) use ($player, $container, $condition) {
                                $this->sendAddedConditionMenu($player, $container, $condition, [$result ? "@form.changed" : "@form.cancelled"]);
                            })->onRecive([$this, "onUpdateCondition"])->show($player);
                        break;
                    case 1:
                        $this->sendConfirmDelete($player, $condition, $container);
                        break;
                    default:
                        $session = Session::getSession($player);
                        $parents = $session->get("parents");
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ConditionContainerForm)->sendConditionList($player, $container);
                        break;
                }
            })->addArgs($container, $condition)->addMessages($messages)->show($player);
    }

    public function onUpdateCondition(Player $player, ?array $data, ConditionContainer $container, Conditionable $condition, callable $callback) {
        if ($data === null) return;

        $datas = $condition->parseFromFormData($data);
        if ($datas["cancel"]) {
            call_user_func_array($callback, [false]);
            return;
        }

        if ($datas["status"] === false) {
            $condition->getEditForm($data, $datas["errors"])
                ->addArgs($container, $condition, $callback)
                ->onRecive([$this, "onUpdateCondition"])
                ->show($player);
            return;
        }
        $condition->parseFromSaveData($datas["contents"]);
        call_user_func_array($callback, [true]);
    }

    public function selectConditionCategory(Player $player, ConditionContainer $container) {
        $buttons = [new Button("@form.back"), new Button("@form.items.category.favorite")];
        $categories = Categories::getConditionCategories();
        foreach ($categories as $category) {
            $buttons[] = new Button("category.".$category);
        }
        (new ListForm(Language::get("form.condition.category.title", [$container->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, ConditionContainer $container, array $categories) {
                if ($data === null) return;

                if ($data === 0) {
                    (new ConditionContainerForm)->sendConditionList($player, $container);
                    return;
                }
                if ($data === 1) {
                    $favorites = Main::getInstance()->getFavorites()->getNested($player->getName().".condition", []);
                    $conditions = [];
                    foreach ($favorites as $favorite) {
                        $condition = ConditionFactory::get($favorite);
                        if ($condition === null) $condition = ScriptFactory::get($favorite);
                        if ($condition === null) continue;

                        $conditions[] = $condition;
                    }
                    $this->sendSelectCondition($player, $container, $conditions, Language::get("form.items.category.favorite"));
                    return;
                }
                $data -= 2;

                $category = $categories[$data];
                $conditions = ConditionFactory::getByCategory($category);
                $conditions = array_merge($conditions, ScriptFactory::getByCategory($category));

                $this->sendSelectCondition($player, $container, $conditions, Categories::getConditionCategories()[$category]);
            })->addArgs($container, array_keys($categories))->show($player);
    }

    public function sendSelectCondition(Player $player, ConditionContainer $container, array $conditions, string $category = "") {
        $buttons = [new Button("@form.back")];
        foreach ($conditions as $condition) {
            $buttons[] = new Button($condition->getName());
        }
        (new ListForm(Language::get("form.condition.select.title", [$container->getName(), $category])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, ConditionContainer $container, array $conditions) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->selectConditionCategory($player, $container);
                    return;
                }
                $data -= 1;

                Session::getSession($player)->set("conditions", $conditions);
                $condition = clone $conditions[$data];
                $this->sendConditionMenu($player, $container, $condition);
            })->addArgs($container, $conditions)->show($player);
    }

    public function sendConditionMenu(Player $player, ConditionContainer $container, Conditionable $condition, array $messages = []) {
        $config = Main::getInstance()->getFavorites();
        $favorites = $config->getNested($player->getName().".condition", []);
        (new ListForm(Language::get("form.condition.menu.title", [$condition->getName()])))
            ->setContent($condition->getDescription())
            ->addButtons([
                new Button("@form.add"),
                new Button(in_array($condition->getId(), $favorites) ? "@form.items.removeFavorite" : "@form.items.addFavorite"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, ConditionContainer $container, Conditionable $condition) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $session = Session::getSession($player);
                        $session->set("parents", array_merge($session->get("parents"), [$container]));
                        if ($condition instanceof ConditionScript) {
                            $container->addCondition($condition);
                            $condition->sendEditForm($player);
                            return;
                        }
                        $condition->getEditForm()
                            ->addArgs($container, $condition, function ($result) use ($player, $container, $condition) {
                                if ($result) {
                                    $container->addCondition($condition);
                                    $this->sendAddedConditionMenu($player, $container, $condition, ["@form.changed"]);
                                } else {
                                    $this->sendConditionMenu($player, $container, $condition, ["@form.cancelled"]);
                                }
                            })->onRecive([$this, "onUpdateCondition"])->show($player);
                        break;
                    case 1:
                        $config = Main::getInstance()->getFavorites();
                        $favorites = $config->getNested($player->getName().".condition", []);
                        if (in_array($condition->getId(), $favorites)) {
                            $favorites = array_diff($favorites, [$condition->getId()]);
                            $favorites = array_values($favorites);
                        } else {
                            $favorites[] = $condition->getId();
                        }
                        $config->setNested($player->getName().".condition", $favorites);
                        $config->save();
                        $this->sendConditionMenu($player, $container, $condition, ["@form.changed"]);
                        break;
                    default:
                        $conditions = Session::getSession($player)->get("conditions");
                        $this->sendSelectCondition($player, $container, $conditions);
                        break;
                }
            })->addArgs($container, $condition)->addMessages($messages)->show($player);
    }

    public function sendConfirmDelete(Player $player, Conditionable $condition, ConditionContainer $container) {
        (new ModalForm(Language::get("form.items.delete.title", [$condition->getName()])))
            ->setContent(Language::get("form.delete.confirm", [trim($condition->getDetail())]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, Conditionable $condition, ConditionContainer $container) {
                if ($data === null) return;

                if ($data) {
                    $index = array_search($condition, $container->getConditions());
                    $container->removeCondition($index);
                    $session = Session::getSession($player);
                    $parents = $session->get("parents");
                    array_pop($parents);
                    $session->set("parents", $parents);
                    (new ConditionContainerForm)->sendConditionList($player, $container, ["@form.delete.success"]);
                } elseif ($container instanceof ConditionScript) {
                    $container->sendEditForm($player, false, ["@form.cancelled"]);
                } else {
                    $this->sendAddedConditionMenu($player, $container, $condition, ["@form.cancelled"]);
                }
            })->addArgs($condition, $container)->show($player);
    }
}