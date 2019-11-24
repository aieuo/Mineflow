<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Button;

class CommandTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, array $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger[1]])))
            ->setContent("type: ".$trigger[0]."\n/".$trigger[1])
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.command.edit.title"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        $this->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    case 2:
                        $manager = Main::getInstance()->getCommandManager();
                        $command = $manager->getCommand($manager->getOriginCommand($trigger[1]));
                        $this->sendCommandMenu($player, $command);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendSelectCommand(Player $player, Recipe $recipe, array $default = [], array $errors = []) {
        (new CustomForm(Language::get("trigger.command.select.title", [$recipe->getName()])))
            ->setContents([
                new Input("@trigger.command.select.input", "@trigger.command.select.placeholder", $default[0] ?? ""),
            ])->onRecive(function (Player $player, ?array $data, Recipe $recipe) {
                if ($data === null) return;

                if (empty($data[0])) {
                    $this->sendSelectCommand($player, $recipe, $data, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getInstance()->getCommandManager();
                $original = $manager->getOriginCommand($data[0]);
                if (!$manager->existsCommand($original)) {
                    $this->sendConfirmCreate($player, $original, function (bool $result) use ($player, $recipe, $data) {
                        if ($result) {
                            (new CommandForm)->sendAddCommand($player, [$data[0]]);
                        } else {
                            $this->sendSelectCommand($player, $recipe, $data, [["@trigger.command.select.notFound", 0]]);
                        }
                    });
                    return;
                }

                $trigger = [TriggerManager::TRIGGER_COMMAND, $data[0]];
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $manager->addRecipe($data[0], $recipe);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->addArgs($recipe)->addErrors($errors)->show($player);
    }

    public function sendConfirmCreate(Player $player, string $name, callable $callback) {
        (new ModalForm("@trigger.command.confirmCreate.title"))
            ->setContent(Language::get("trigger.command.confirmCreate.content", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, callable $callback) {
                if ($data === null) return;
                call_user_func_array($callback, [$data]);
            })->addArgs($callback)->show($player);
    }

    public function sendConfirmDelete(Player $player, Recipe $recipe, array $trigger) {
        (new ModalForm(Language::get("form.items.delete.title", [$recipe->getName(), $trigger[1]])))
            ->setContent(Language::get("form.delete.confirm", [$trigger[0].": ".$trigger[1]]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                if ($data) {
                    $recipe->removeTrigger($trigger);
                    $manager = Main::getInstance()->getCommandManager();
                    $count = $manager->removeRecipe($manager->getOriginCommand($trigger[1]), $recipe);
                    if ($count === 0) {
                        $manager->removeCommand($manager->getOriginCommand($trigger[1]));
                    }
                    (new RecipeForm)->sendTriggerList($player, $recipe, ["@form.delete.success"]);
                } else {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@form.cancelled"]);
                }
            })->addArgs($recipe, $trigger)->show($player);
    }
}