<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\trigger\Trigger;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []) {
        switch ($trigger->getType()) {
            case Trigger::TYPE_BLOCK:
                (new BlockTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
            case Trigger::TYPE_EVENT:
                (new EventTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
            case Trigger::TYPE_COMMAND:
                (new CommandTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
            case Trigger::TYPE_FORM:
                (new FormTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
                return;
        }
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent("type: @trigger.type.".$trigger->getType()."\n".$trigger->getKey())
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, ?int $data, Recipe $recipe, Trigger $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        $this->sendConfirmDelete($player, $recipe, $trigger);
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
                new Button("@trigger.type.command"),
                new Button("@trigger.type.form"),
            ])->onReceive(function (Player $player, ?int $data, Recipe $recipe) {
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
                    case 3:
                        (new CommandTriggerForm)->sendSelectCommand($player, $recipe);
                        break;
                    case 4:
                        (new FormTriggerForm)->sendSelectForm($player, $recipe);
                        break;
                }
            })->addArgs($recipe)->show($player);
    }

    public function sendConfirmDelete(Player $player, Recipe $recipe, Trigger $trigger) {
        (new ModalForm(Language::get("form.items.delete.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent(Language::get("form.delete.confirm", [$trigger->getType().": ".$trigger->getKey()]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, Recipe $recipe, Trigger $trigger) {
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