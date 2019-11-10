<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\action\process\ProcessFactory;
use aieuo\mineflow\action\script\ScriptFactory;
use aieuo\mineflow\FormAPI\element\Button;
use aieuo\mineflow\utils\Categories;

class ActionForm {
    public function sendAddedActionMenu(Player $player, Recipe $recipe, Action $action, array $messages = []) {
        (new ListForm(Language::get("form.action.addedActionMenu.title", [$recipe->getName(), $action->getName()])))
            ->setContent($action->getDetail())
            ->addButtons([// TODO: 移動させるボタン
                new Button("@form.edit"),
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, Action $action) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $action->getEditForm()
                            ->addArgs($recipe, $action, [[$this, "sendAddedActionMenu"], [$player, $recipe, $action]])
                            ->onRecive([$this, "onUpdateAction"])
                            ->show($player);
                        break;
                    case 1:
                        $this->sendConfirmDelete($player, $action, function ($result) use ($player, $recipe, $action) {
                            if ($result) {
                                $index = Session::getSession($player)->get("actions_selected");
                                $recipe->removeAction($index);
                                (new RecipeForm)->sendActionList($player, $recipe, ["@form.delete.success"]);
                            } else {
                                $this->sendAddedActionMenu($player, $recipe, $action, ["@form.cancelled"]);
                            }
                        });
                        break;
                    default:
                        (new RecipeForm)->sendActionList($player, $recipe);
                        break;
                }
            })->addArgs($recipe, $action)->addMessages($messages)->show($player);
    }

    public function onUpdateAction(Player $player, ?array $data, Recipe $recipe, Action $action, array $onChanged, ?array $onCancelled = null) {
        if ($data === null) return;

        $onCancelled = $onCancelled ?? $onChanged;
        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            call_user_func_array($onCancelled[0], array_merge($onCancelled[1], [["@form.cancelled"]]));
            return;
        }

        if ($datas["status"] === false) {
            $action->getEditForm($data, $datas["errors"])
                ->addArgs($recipe, $action, $onChanged, $onCancelled)
                ->onRecive([$this, "onUpdateAction"])
                ->show($player);
            return;
        }
        $action->parseFromSaveData($datas["contents"]);
        call_user_func_array($onChanged[0], array_merge($onChanged[1], [["@form.changed"]]));
    }

    public function selectActionCategory(Player $player, Recipe $recipe) {
        $buttons = [new Button("@form.back"), new Button("@form.action.category.favorite")];
        $categories = Categories::getActionCategories();
        foreach ($categories as $category) {
            $buttons[] = new Button($category);
        }
        (new ListForm("@form.action.category.title"))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $categories) {
                if ($data === null) return;

                if ($data === 0) {
                    (new RecipeForm)->sendActionList($player, $recipe);
                    return;
                }
                if ($data === 1) {
                    return;
                }
                $data -= 2;

                $category = $categories[$data];
                $actions = ProcessFactory::getByCategory($category);
                $actions = array_merge($actions, ScriptFactory::getByCategory($category));
                Session::getSession($player)->set("category_name", Categories::getActionCategories()[$category]);
                $this->sendSelectAction($player, $recipe, $actions);
            })->addArgs($recipe, array_keys($categories))->show($player);
    }

    public function sendSelectAction(Player $player, Recipe $recipe, array $actions) {
        $category = Session::getSession($player)->get("category_name");
        $buttons = [new Button("@form.back")];
        foreach ($actions as $action) {
            $buttons[] = new Button($action->getName());
        }
        (new ListForm(Language::get("form.action.select.title", [$category])))
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $actions) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->selectActionCategory($player, $recipe);
                    return;
                }
                $data -= 1;

                Session::getSession($player)->set("actions", $actions);
                $action = clone $actions[$data];
                $this->sendActionMenu($player, $recipe, $action);
            })->addArgs($recipe, $actions)->show($player);
    }

    public function sendActionMenu(Player $player, Recipe $recipe, Action $action, array $messages = []) {
        (new ListForm(Language::get("form.action.menu.title", [$action->getName()])))
            ->setContent($action->getDetail())
            ->addButtons([
                new Button("@form.add"),
                new Button("@form.action.addFavorite"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, Action $action) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $recipe->addAction($action);
                        $action->getEditForm()
                            ->addArgs($recipe, $action, [[$this, "sendAddedActionMenu"], [$player, $recipe, $action]], [[$this, "sendActionMenu"], [$player, $recipe, $action]])
                            ->onRecive([$this, "onUpdateAction"])
                            ->show($player);
                        break;
                    case 1:
                        break;
                    default:
                        $actions = Session::getSession($player)->get("actions");
                        $this->sendSelectAction($player, $recipe, $actions);
                        break;
                }
            })->addArgs($recipe, $action)->addMessages($messages)->show($player);
    }

    public function sendConfirmDelete(Player $player, Action $action, callable $callback) {
        (new ModalForm(Language::get("form.recipe.delete.title", [$action->getName()])))
            ->setContent(Language::get("form.confirmDelete", [$action->getDetail()]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, callable $callback) {
                if ($data === null) return;
                call_user_func_array($callback, [$data]);
            })->addArgs($callback)->show($player);
    }
}