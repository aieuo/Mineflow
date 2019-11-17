<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;

class TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, array $trigger, array $messages = []) {
        switch ($trigger[0]) {
            case TriggerManager::TRIGGER_BLOCK:
                (new BlockTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
            case TriggerManager::TRIGGER_EVENT:
                (new EventTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
        }
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
                new Button("@trigger.type.event"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new BlockTriggerForm)->sendMenu($player, $recipe);
                        break;
                    case 2:
                        (new EventTriggerForm)->sendEventTriggerList($player, $recipe);
                        break;
                }
            })->addArgs($recipe)->show($player);
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