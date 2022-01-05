<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class CustomTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), (string)$trigger])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back", fn() => (new RecipeForm)->sendTriggerList($player, $recipe)),
                new Button("@form.delete", fn() => (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger)),
            ])->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new CustomForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::CUSTOM])))
            ->addContents([
                new ExampleInput("@trigger.custom.name", "aieuo", "", true),
                new CancelToggle(fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)),
            ])->onReceive(function (Player $player, array $data) use($recipe) {
                $trigger = CustomTrigger::create($data[0]);
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->show($player);
    }
}