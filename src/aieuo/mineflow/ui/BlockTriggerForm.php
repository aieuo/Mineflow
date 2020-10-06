<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerTypes;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class BlockTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent("type: @trigger.type.".$trigger->getType()."\n".$trigger->getKey())
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.block.warp"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe, Trigger $trigger) {
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    case 2:
                        $pos = explode(",", $trigger->getKey());
                        $level = Server::getInstance()->getLevelByName($pos[3]);
                        if ($level === null) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.level.notfound"]);
                            return;
                        }
                        $player->teleport(new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $level));
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), TriggerTypes::BLOCK])))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe) {
                if ($data === 0) {
                    (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }

                Session::getSession($player)->set("blockTriggerAction", "add")->set("blockTriggerRecipe", $recipe);
                $player->sendMessage(Language::get("trigger.block.add.touch"));
            })->addArgs($recipe)->show($player);
    }
}