<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\trigger\Trigger;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use pocketmine\level\Position;
use pocketmine\Server;

class BlockTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent("type: @trigger.type.".$trigger->getType()."\n".$trigger->getKey())
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.block.warp"),
            ])->onReceive(function (Player $player, ?int $data, Recipe $recipe, Trigger $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new TriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
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

    public function sendMenu(Player $player, Recipe $recipe) {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Trigger::TYPE_BLOCK])))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onReceive(function (Player $player, ?int $data, Recipe $recipe) {
                if ($data === null) return;

                if ($data === 0) {
                    (new TriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }

                Session::getSession($player)->set("blockTriggerAction", "add")->set("blockTriggerRecipe", $recipe);
                $player->sendMessage(Language::get("trigger.block.add.touch"));
            })->addArgs($recipe)->show($player);
    }
}