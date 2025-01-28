<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

abstract class TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        $form = (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getType()])));
        $form->setContent((string)$trigger);
        $form->addButtons([
            new Button("@form.back", fn() => (new RecipeForm)->sendTriggerList($player, $recipe)),
            new Button("@form.delete", fn() => (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger)),
        ]);
        $this->buildAddedTriggerMenu($form, $player, $recipe, $trigger);
        $form->addMessages($messages);
        $form->show($player);
    }

    public function buildAddedTriggerMenu(ListForm $form, Player $player, Recipe $recipe, Trigger $trigger): void {
    }

    abstract public function sendMenu(Player $player, Recipe $recipe): void;
}