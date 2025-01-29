<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;

class CommandForm {

    public function sendMenu(Player $player, array $messages = []): void {
        (new ListForm("@form.command.menu.title"))
            ->addButtons([
                new Button("@form.back", fn() => (new HomeForm)->sendMenu($player)),
                new Button("@form.add", fn() => $this->sendAddCommand($player)),
                new Button("@form.edit", fn() => $this->sendSelectCommand($player)),
                new Button("@form.command.menu.commandList", fn() => $this->sendCommandList($player)),
            ])->addMessages($messages)->show($player);
    }

    public function sendAddCommand(Player $player, array $defaults = [], ?callable $callback = null): void {
        (new CustomForm("@form.command.addCommand.title"))
            ->setContents([
                new Input("@form.command.menu.title", "@trigger.command.select.placeholder", $defaults[0] ?? "", true),
                new Input("@form.command.description", "", $defaults[1] ?? ""),
                new Dropdown("@form.command.permission", [
                    Language::get("form.command.addCommand.permission.op"),
                    Language::get("form.command.addCommand.permission.true"),
                    Language::get("form.command.addCommand.permission.custom"),
                ], $defaults[2] ?? 0),
                new CancelToggle(fn() => $callback === null ? $this->sendMenu($player) : $callback(false, null)),
            ])->onReceive(function (Player $player, array $data) use($callback) {
                $manager = Mineflow::getCommandManager();
                $original = $manager->getCommandLabel($data[0]);
                if (!$manager->isSubcommand($data[0]) and $manager->existsCommand($original)) {
                    throw new InvalidFormValueException("@form.command.alreadyExists", 0);
                }
                if ($manager->isRegistered($original)) {
                    throw new InvalidFormValueException("@form.command.alreadyUsed", 0);
                }

                $permission = ["mineflow.customcommand.op", "mineflow.customcommand.true"][$data[2]] ?? "mineflow.customcommand.op";

                $manager->addCommand($data[0], $permission, $data[1]);
                $command = $manager->getCommand($original);
                Session::getSession($player)->set("command_menu_prev", [$this, "sendMenu"]);

                if ($data[2] === 2) {
                    $this->sendSelectPermissionName($player, $command, $callback);
                    return;
                }
                if ($callback === null) {
                    $this->sendCommandMenu($player, $command);
                } else {
                    $callback(true, $command);
                }
            })->show($player);
    }

    public function sendSelectCommand(Player $player, array $defaults = []): void {
        (new CustomForm("@form.command.select.title"))
            ->setContents([
                new Input("@form.command.name", "", $defaults[0] ?? "", true),
                new CancelToggle(fn() => $this->sendMenu($player)),
            ])->onReceive(function (Player $player, array $data) {
                $manager = Mineflow::getCommandManager();
                if (!$manager->existsCommand($manager->getCommandLabel($data[0]))) {
                    throw new InvalidFormValueException("@form.command.notFound", 0);
                }

                $command = $manager->getCommand($manager->getCommandLabel($data[0]));
                Session::getSession($player)->set("command_menu_prev", $this->sendSelectCommand(...));
                $this->sendCommandMenu($player, $command);
            })->show($player);
    }

    public function sendCommandList(Player $player): void {
        $manager = Mineflow::getCommandManager();
        $commands = $manager->getCommandAll();
        $buttons = [new Button("@form.back", fn() => $this->sendMenu($player))];
        foreach ($commands as $command) {
            $buttons[] = new Button("/".$command["command"], function () use($player, $command) {
                Session::getSession($player)->set("command_menu_prev", [$this, "sendCommandList"]);
                $this->sendCommandMenu($player, $command);
            });
        }

        (new ListForm("@form.command.commandList.title"))
            ->addButtons($buttons)
            ->show($player);
    }

    public function sendCommandMenu(Player $player, array $command, array $messages = []): void {
        $permissions = [
            "mineflow.customcommand.op" => "@form.command.addCommand.permission.op",
            "mineflow.customcommand.true" => "@form.command.addCommand.permission.true"
        ];
        $permission = $permissions[$command["permission"]] ?? $command["permission"];
        (new ListForm("/".$command["command"]))
            ->setContent("/".$command["command"]."\n".Language::get("form.command.permission").": ".$permission."\n".Language::get("form.command.description").": ".$command["description"])
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.command.commandMenu.editDescription"),
                new Button("@form.command.commandMenu.editPermission"),
                new Button("@form.command.recipes"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, array $command) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("command_menu_prev");
                        if (is_callable($prev)) $prev($player);
                        else $this->sendMenu($player);
                        break;
                    case 1:
                        $this->changeDescription($player, $command);
                        break;
                    case 2:
                        $this->changePermission($player, $command);
                        break;
                    case 3:
                        $this->sendRecipeList($player, $command);
                        break;
                    case 4:
                        $this->sendConfirmDelete($player, $command);
                        break;
                }
            })->addArgs($command)->addMessages($messages)->show($player);
    }

    public function changeDescription(Player $player, array $command): void {
        (new CustomForm(Language::get("form.command.changeDescription.title", ["/".$command["command"]])))
            ->setContents([
                new Input("@form.command.description", "", $command["description"] ?? ""),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, array $command) {
                if ($data[1]) {
                    $this->sendCommandMenu($player, $command);
                    return;
                }

                $manager = Mineflow::getCommandManager();
                $command["description"] = $data[0];
                $manager->updateCommand($command);
                $this->sendCommandMenu($player, $command);
            })->addArgs($command)->show($player);
    }

    public function changePermission(Player $player, array $command): void {
        $permissions = ["mineflow.customcommand.op" => 0, "mineflow.customcommand.true" => 1];
        (new CustomForm(Language::get("form.command.changePermission.title", ["/".$command["command"]])))
            ->setContents([
                new Dropdown("@form.command.permission", [
                    Language::get("form.command.addCommand.permission.op"),
                    Language::get("form.command.addCommand.permission.true"),
                    Language::get("form.command.addCommand.permission.custom"),
                ], $permissions[$command["permission"]] ?? 2),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, array $command) {
                if ($data[1]) {
                    $this->sendCommandMenu($player, $command);
                    return;
                }

                if ($data[0] === 2) {
                    $this->sendSelectPermissionName($player, $command);
                    return;
                }

                $manager = Mineflow::getCommandManager();
                $command["permission"] = ["mineflow.customcommand.op", "mineflow.customcommand.true"][$data[0]];
                $manager->updateCommand($command);
                $this->sendCommandMenu($player, $command);
            })->addArgs($command)->show($player);
    }

    public function sendSelectPermissionName(Player $player, array $command, ?callable $callback = null): void {
        (new CustomForm(Language::get("form.command.changePermission.title", ["/".$command["command"]])))
            ->setContents([
                new Input("@form.command.addCommand.permission.custom.input", "", $command["permission"], true),
                new CancelToggle(fn() => $callback === null ? $this->changePermission($player, $command) : $callback(false)),
            ])->onReceive(function (Player $player, array $data, array $command) use($callback) {
                $manager = Mineflow::getCommandManager();
                $command["permission"] = $data[0];
                $manager->updateCommand($command);
                if ($callback === null) {
                    $this->sendCommandMenu($player, $command);
                } else {
                    $callback(true);
                }
            })->addArgs($command)->show($player);
    }

    public function sendConfirmDelete(Player $player, array $command): void {
        (new ModalForm(Language::get("form.command.delete.title", ["/".$command["command"]])))
            ->setContent(Language::get("form.delete.confirm", ["/".$command["command"]]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, array $command) {
                if ($data) {
                    $commandManager = Mineflow::getCommandManager();
                    $recipeManager = Mineflow::getRecipeManager();

                    $recipes = Mineflow::getCommandManager()->getAssignedRecipes($command["command"]);
                    foreach ($recipes as $recipe => $commands) {
                        [$name, $group] = $recipeManager->parseName($recipe);

                        $recipe = $recipeManager->get($name, $group);
                        if ($recipe === null) continue;

                        foreach ($commands as $cmd) {
                            $recipe->removeTrigger(new CommandTrigger($cmd));
                        }
                    }
                    $commandManager->removeCommand($command["command"]);
                    $this->sendMenu($player, ["@form.deleted"]);
                } else {
                    $this->sendCommandMenu($player, $command, ["@form.cancelled"]);
                }
            })->addArgs($command)->show($player);
    }

    public function sendRecipeList(Player $player, array $command, array $messages = []): void {
        $buttons = [new Button("@form.back"), new Button("@form.add")];

        $recipes = Mineflow::getCommandManager()->getAssignedRecipes($command["command"]);
        foreach ($recipes as $name => $commands) {
            $buttons[] = new Button($name." | ".count($commands));
        }
        (new ListForm(Language::get("form.recipes.title", ["/".$command["command"]])))
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, array $command, array $recipes) {
                switch ($data) {
                    case 0:
                        $this->sendCommandMenu($player, $command);
                        return;
                    case 1:
                        (new MineflowForm)->selectRecipe($player, Language::get("form.recipes.add", [$command["command"]]),
                            function (Recipe $recipe) use ($player, $command) {
                                $trigger = new CommandTrigger($command["command"]);
                                if ($recipe->existsTrigger($trigger)) {
                                    $this->sendRecipeList($player, $command, ["@trigger.alreadyExists"]);
                                    return;
                                }
                                $recipe->addTrigger($trigger);
                                $this->sendRecipeList($player, $command, ["@form.added"]);
                            },
                            fn() => $this->sendRecipeList($player, $command)
                        );
                        return;
                }
                $data -= 2;

                $this->sendRecipeMenu($player, $command, $data, $recipes);
            })->addMessages($messages)->addArgs($command, $recipes)->show($player);
    }

    public function sendRecipeMenu(Player $player, array $commandData, int $index, array $recipes): void {
        $command = Mineflow::getCommandManager()->getCommand($commandData["command"]);
        $triggers = array_values($recipes)[$index];
        $content = implode("\n", array_map(function (String $cmd) {
            return "/".$cmd;
        }, $triggers));
        (new ListForm(Language::get("form.recipes.title", ["/".$command["command"]])))
            ->setContent($content)
            ->setButtons([
                new Button("@form.back", fn() => $this->sendRecipeList($player, $command)),
                new Button("@form.edit", function () use($player, $command, $recipes, $index) {
                    Session::getSession($player)->set("recipe_menu_prev", function() use($player, $command, $index, $recipes) {
                        $this->sendRecipeMenu($player, $command, $index, $recipes);
                    });
                    $recipeName = array_keys($recipes)[$index];
                    [$name, $group] = Mineflow::getRecipeManager()->parseName($recipeName);
                    $recipe = Mineflow::getRecipeManager()->get($name, $group);
                    (new RecipeForm())->sendTriggerList($player, $recipe);
                })
            ])->show($player);
    }
}