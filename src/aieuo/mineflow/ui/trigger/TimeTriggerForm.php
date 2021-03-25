<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\time\TimeTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class TimeTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey().":".$trigger->getSubKey()])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back", function () use($player, $recipe) { (new RecipeForm)->sendTriggerList($player, $recipe); }),
                new Button("@form.delete", function () use($player, $recipe, $trigger) { (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger); }),
            ])->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new CustomForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::TIME])))
            ->addContents([
                new ExampleNumberInput("@trigger.time.hours", "12", "", true, 0, 23),
                new ExampleNumberInput("@trigger.time.minutes", "0", "", true, 0, 59),
                new CancelToggle(function () use($player, $recipe) { (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe); }),
            ])->onReceive(function (Player $player, array $data) use($recipe) {
                $trigger = TimeTrigger::create((string)((int)$data[0]), (string)((int)$data[1]));
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->show($player);
    }
}