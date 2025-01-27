<?php

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\BaseTriggerForm;
use aieuo\mineflow\trigger\TriggerForm;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class TimeTriggerForm extends TriggerForm {

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new CustomForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::TIME])))
            ->addContents([
                new ExampleNumberInput("@trigger.time.hours", "12", "", true, 0, 23),
                new ExampleNumberInput("@trigger.time.minutes", "0", "", true, 0, 59),
                new CancelToggle(fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)),
            ])->onReceive(function (Player $player, array $data) use($recipe) {
                $trigger = new TimeTrigger((int)$data[0], (int)$data[1]);
                (new BaseTriggerForm)->tryAddTriggerToRecipe($player, $recipe, $trigger);
            })->show($player);
    }
}