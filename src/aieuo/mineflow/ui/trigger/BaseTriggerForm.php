<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class BaseTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        $form = Triggers::getForm($trigger->getType());
        if ($form !== null) {
            $form->sendAddedTriggerMenu($player, $recipe, $trigger);
            return;
        }
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent("type: @trigger.type.".$trigger->getType()."\n".$trigger->getKey())
            ->addButtons([
                new Button("@form.back", function () use($player, $recipe) { (new RecipeForm)->sendTriggerList($player, $recipe); }),
                new Button("@form.delete", function () use($player, $recipe, $trigger) { $this->sendConfirmDelete($player, $recipe, $trigger); }),
            ])->addMessages($messages)->show($player);
    }

    public function sendSelectTriggerType(Player $player, Recipe $recipe): void {
        $buttons = [
            new Button("@form.back", function () use($player, $recipe) { (new RecipeForm)->sendTriggerList($player, $recipe); }),
        ];
        foreach (Triggers::getAllForm() as $type => $form) {
            $buttons[] = new Button("@trigger.type.".$type, function () use($player, $recipe, $form) {
                $form->sendMenu($player, $recipe);
            });
        }
        (new ListForm(Language::get("form.trigger.selectTriggerType", [$recipe->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->addArgs($recipe)
            ->show($player);
    }

    public function sendConfirmDelete(Player $player, Recipe $recipe, Trigger $trigger): void {
        (new ModalForm(Language::get("form.items.delete.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent(Language::get("form.delete.confirm", [$trigger->getType().": ".$trigger->getKey()]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, Recipe $recipe, Trigger $trigger) {
                if ($data) {
                    $recipe->removeTrigger($trigger);
                    (new RecipeForm)->sendTriggerList($player, $recipe, ["@form.delete.success"]);
                } else {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@form.cancelled"]);
                }
            })->addArgs($recipe, $trigger)->show($player);
    }
}