<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class BlockTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back", function () use($player, $recipe) { (new RecipeForm)->sendTriggerList($player, $recipe); }),
                new Button("@form.delete", function () use($player, $recipe, $trigger) { (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger); }),
                new Button("@trigger.block.warp", function () use($player, $recipe, $trigger) {
                    $pos = explode(",", $trigger->getKey());
                    $level = Server::getInstance()->getLevelByName($pos[3]);
                    if ($level === null) {
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.world.notfound"]);
                        return;
                    }
                    $player->teleport(new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $level));
                }),
            ])->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::BLOCK])))
            ->addButtons([
                new Button("@form.back", function () use($player, $recipe) { (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe); }),
                new Button("@form.add", function () use($player, $recipe) {
                    Session::getSession($player)->set("blockTriggerAction", "add")->set("blockTriggerRecipe", $recipe);
                    $player->sendMessage(Language::get("trigger.block.add.touch"));
                }),
            ])->show($player);
    }
}