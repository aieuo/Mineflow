<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\custom;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\BaseTriggerForm;
use aieuo\mineflow\trigger\TriggerForm;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class CustomTriggerForm extends TriggerForm {

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new CustomForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::CUSTOM])))
            ->addContents([
                new ExampleInput("@trigger.custom.name", "aieuo", "", true),
                new CancelToggle(fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)),
            ])->onReceive(function (Player $player, array $data) use($recipe) {
                $trigger = new CustomTrigger($data[0]);
                (new BaseTriggerForm)->tryAddTriggerToRecipe($player, $recipe, $trigger);
            })->show($player);
    }
}