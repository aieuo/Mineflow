<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\FormAPI\element\Button;

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
                            ->addArgs($recipe, $action)
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

    public function onUpdateAction(Player $player, ?array $data, Recipe $recipe, Action $action) {
        if ($data === null) return;

        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendAddedActionMenu($player, $recipe, $action, ["@form.cancelled"]);
            return;
        }

        if ($datas["status"] === false) {
            $action->getEditForm($data, $datas["errors"])
                ->addArgs($recipe, $action)
                ->onRecive([$this, "onUpdateAction"])
                ->show($player);
            return;
        }
        $action->parseFromSaveData($datas["contents"]);
        $this->sendAddedActionMenu($player, $recipe, $action, ["@form.changed"]);
    }

    public function selectAction(Player $player, Recipe $recipe) {

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