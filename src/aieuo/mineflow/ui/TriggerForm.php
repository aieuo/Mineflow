<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;

class TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, array $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger[1]])))
            ->setContent("type: ".$trigger[0]."\n".$trigger[1])
            ->addButtons([
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        $this->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    default:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendSelectTriggerType(Player $player, Recipe $recipe) {
        (new ListForm(Language::get("form.trigger.selectTriggerType", [$recipe->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@trigger.type.block"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        $this->sendTriggerMenu($player, $recipe, TriggerManager::TRIGGER_BLOCK);
                        break;
                }
            })->addArgs($recipe)->show($player);
    }

    public function sendTriggerMenu(Player $player, Recipe $recipe, string $type) {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), $type])))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, string $type) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->sendSelectTriggerType($player, $recipe);
                    return;
                }

                switch ($type) {
                    case TriggerManager::TRIGGER_BLOCK:
                        Session::getSession($player)->set("blockTriggerAction", "add")->set("blockTriggerRecipe", $recipe);
                        $player->sendMessage(Language::get("trigger.block.add.touch"));
                        break;
                }
            })->addArgs($recipe, $type)->show($player);
    }

    public function sendConfirmDelete(Player $player, Recipe $recipe, array $trigger) {
        (new ModalForm(Language::get("form.items.delete.title", [$recipe->getName(), $trigger[1]])))
            ->setContent(Language::get("form.delete.confirm", [$trigger[0].": ".$trigger[1]]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                if ($data) {
                    $recipe->removeTrigger($trigger);
                    (new RecipeForm)->sendTriggerList($player, $recipe, ["@form.delete.success"]);
                } else {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@form.cancelled"]);
                }
            })->addArgs($recipe, $trigger)->show($player);
    }
}