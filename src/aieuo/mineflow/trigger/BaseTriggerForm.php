<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class BaseTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        $form = Triggers::getForm($trigger->getType());
        if ($form !== null) {
            $form->sendAddedTriggerMenu($player, $recipe, $trigger);
            return;
        }

        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getType()])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back", fn() => (new RecipeForm)->sendTriggerList($player, $recipe)),
                new Button("@form.delete", fn() => $this->sendConfirmDelete($player, $recipe, $trigger)),
            ])->addMessages($messages)->show($player);
    }

    public function tryAddTriggerToRecipe(Player $player, Recipe $recipe, Trigger $trigger): bool {
        if ($recipe->existsTrigger($trigger)) {
            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
            return false;
        }

        $recipe->addTrigger($trigger);
        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
        return true;
    }

    public function sendSelectTriggerType(Player $player, Recipe $recipe): void {
        (new ListForm(Language::get("form.trigger.selectTriggerType", [$recipe->getName()])))
            ->addButton(new Button("@form.back", fn() => (new RecipeForm)->sendTriggerList($player, $recipe)))
            ->addButtonsEach(Triggers::getAllForm(), function (TriggerForm $form, string $type) use($player, $recipe) {
                return new Button("@trigger.type.".$type, fn() => $form->sendMenu($player, $recipe));
            })->show($player);
    }

    public function sendConfirmDelete(Player $player, Recipe $recipe, Trigger $trigger): void {
        (new ModalForm(Language::get("form.items.delete.title", [$recipe->getName(), (string)$trigger])))
            ->setContent(Language::get("form.delete.confirm", [(string)$trigger]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data) use($recipe, $trigger) {
                if ($data) {
                    $recipe->removeTrigger($trigger);
                    (new RecipeForm)->sendTriggerList($player, $recipe, ["@form.deleted"]);
                } else {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@form.cancelled"]);
                }
            })->show($player);
    }
}