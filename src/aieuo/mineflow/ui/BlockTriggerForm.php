<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;
use pocketmine\level\Position;
use pocketmine\Server;

class BlockTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, array $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger[1]])))
            ->setContent("type: @trigger.type.".$trigger[0]."\n".$trigger[1])
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.block.warp"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new TriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    case 2:
                        $datas = explode(",", $trigger[1]);
                        $level = Server::getInstance()->getLevelByName($datas[3]);
                        if ($level === null) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.level.notfound"]);
                            return;
                        }
                        $pos = new Position((int)$datas[0], (int)$datas[1], (int)$datas[2], $level);
                        $player->teleport($pos);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe) {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), TriggerManager::TRIGGER_BLOCK])))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe) {
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