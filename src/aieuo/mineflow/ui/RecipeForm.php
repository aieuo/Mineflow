<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\trigger\Trigger;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Button;

class RecipeForm {

    public function sendMenu(Player $player, array $messages = []) {
        (new ListForm("@mineflow.recipe"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back", function () use($player) { (new HomeForm)->sendMenu($player); }),
                new Button("@form.add", function () use($player) { $this->sendAddRecipe($player); }),
                new Button("@form.edit", function () use($player) { $this->sendSelectRecipe($player); }),
                new Button("@form.recipe.menu.recipeList", function () use($player) { $this->sendRecipeList($player); }),
                new Button("@mineflow.export", function () use($player) { (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title", [new ExportForm, "sendRecipeListByRecipe"]); }),
                new Button("@mineflow.import", function () use($player) { (new ImportForm)->sendSelectImportFile($player); }),
            ])->addMessages($messages)->show($player);
    }

    public function sendAddRecipe(Player $player, array $default = [], array $errors = []) {
        $manager = Main::getRecipeManager();
        $name = $manager->getNotDuplicatedName("recipe");

        (new CustomForm("@form.recipe.addRecipe.title"))->setContents([
                new Input("@form.recipe.recipeName", $name, $default[0] ?? ""),
                new Input("@form.recipe.groupName", "", $default[1] ?? ""),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, string $defaultName) {
                if ($data[2]) {
                    $this->sendMenu($player);
                    return;
                }

                $manager = Main::getRecipeManager();
                $name = $data[0] === "" ? $defaultName : $data[0];
                $group = $data[1];

                $errors = [];
                if (preg_match("#[.¥/:?<>|*\"]#", preg_quote($name))) $errors[] = ["@form.recipe.invalidName", 0];
                if (preg_match("#[.¥:?<>|*\"]#", preg_quote($group))) $errors[] = ["@form.recipe.invalidName", 1];
                if (!empty($errors)) {
                    $this->sendAddRecipe($player, $data, $errors);
                    return;
                }

                if ($manager->exists($name, $group)) {
                    $newName = $manager->getNotDuplicatedName($name, $group);
                    (new MineflowForm)->confirmRename($player, $name, $newName,
                        function (Player $player, string $name) use ($data) {
                            $manager = Main::getRecipeManager();
                            $recipe = new Recipe($name, $data[1], $player->getName());
                            $manager->add($recipe);
                            Session::getSession($player)
                                ->set("recipe_menu_prev", [$this, "sendRecipeList"])
                                ->set("recipe_menu_prev_data", [$recipe->getGroup()]);
                            $this->sendRecipeMenu($player, $recipe);
                        },
                        function (Player $player, string $name) use ($data) {
                            $this->sendAddRecipe($player, $data, [[Language::get("form.recipe.exists", [$name]), 0]]);
                        });
                    return;
                }

                $recipe = new Recipe($name, $group, $player->getName());
                if (file_exists($recipe->getFileName($manager->getSaveDir()))) {
                    $this->sendAddRecipe($player, $data, [[Language::get("form.recipe.exists", [$name]), 0]]);
                    return;
                }

                $manager->add($recipe);
                Session::getSession($player)
                    ->set("recipe_menu_prev", [$this, "sendRecipeList"])
                    ->set("recipe_menu_prev_data", [$recipe->getGroup()]);
                $this->sendRecipeMenu($player, $recipe);
            })->addErrors($errors)->addArgs($name)->show($player);
    }

    public function sendSelectRecipe(Player $player, array $default = [], array $errors = []) {
        (new MineflowForm)->selectRecipe($player, "@form.recipe.select.title",
            function (Player $player, Recipe $recipe) {
                Session::getSession($player)
                    ->set("recipe_menu_prev", [$this, "sendRecipeList"])
                    ->set("recipe_menu_prev_data", [$recipe->getGroup()]);
                $this->sendRecipeMenu($player, $recipe);
            },
            function (Player $player) {
                $this->sendMenu($player);
            }, $default, $errors);
    }

    public function sendRecipeList(Player $player, string $path = "") {
        $manager = Main::getRecipeManager();
        $recipeGroups = $manager->getByPath($path);
        $buttons = [new Button("@form.back")];
        $recipes = $recipeGroups[$path] ?? [];
        foreach ($recipes as $recipe) {
            $buttons[] = new Button($recipe->getName());
        }
        unset($recipeGroups[$path]);

        $groups = [];
        foreach ($recipeGroups as $group => $value) {
            if ($path !== "") {
                $name = explode("/", str_replace($path."/", "", $group))[0];
            } else {
                $name = explode("/", $group)[0];
            }

            if (!isset($groups[$name])) {
                $buttons[] = new Button("[$name]");
                $groups[$name] = $path !== "" ? $path."/".$name : $name;
            }
        }
        $recipeGroups = array_merge($recipes, array_values($groups));

        (new ListForm("@form.recipe.recipeList.title"))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, string $path, array $recipes) {
                if ($data === 0) {
                    if ($path === "") {
                        $this->sendMenu($player);
                        return;
                    }
                    $paths = explode("/", $path);
                    array_pop($paths);
                    $this->sendRecipeList($player, implode("/", $paths));
                    return;
                }
                $data --;

                $recipe = array_values($recipes)[$data];
                if ($recipe instanceof Recipe) {
                    Session::getSession($player)
                        ->set("recipe_menu_prev", [$this, "sendRecipeList"])
                        ->set("recipe_menu_prev_data", [$path]);
                    $this->sendRecipeMenu($player, $recipe);
                    return;
                }
                $this->sendRecipeList($player, $recipe);
            })->addArgs($path, $recipeGroups)->show($player);
    }

    public function sendRecipeMenu(Player $player, Recipe $recipe, array $messages = []) {
        $detail = trim($recipe->getDetail());
        (new ListForm(Language::get("form.recipe.recipeMenu.title", [$recipe->getPathname()])))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@action.edit"),
                new Button("@form.recipe.recipeMenu.changeName"),
                new Button("@form.recipe.recipeMenu.execute"),
                new Button("@form.recipe.recipeMenu.setTrigger"),
                new Button("@form.recipe.args.return.set"),
                new Button("@form.recipe.changeTarget"),
                new Button("@form.recipe.recipeMenu.save"),
                new Button("@mineflow.export"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("recipe_menu_prev");
                        if (is_callable($prev)) {
                            $data = array_merge([$player], Session::getSession($player)->get("recipe_menu_prev_data", []));
                            call_user_func_array($prev, $data);
                        }
                        else $this->sendMenu($player);
                        break;
                    case 1:
                        Session::getSession($player)->set("parents", []);
                        (new FlowItemContainerForm)->sendActionList($player, $recipe, FlowItemContainer::ACTION);
                        break;
                    case 2:
                        $this->sendChangeName($player, $recipe);
                        break;
                    case 3:
                        $recipe->executeAllTargets($player);
                        break;
                    case 4:
                        $this->sendTriggerList($player, $recipe);
                        break;
                    case 5:
                        (new ListForm("@form.recipe.args.return.set"))
                            ->setContent("@form.selectButton")
                            ->setButtons([
                                new Button("@form.back"),
                                new Button("@form.recipe.args.set"),
                                new Button("@form.recipe.returnValue.set"),
                            ])->onReceive(function (Player $player, int $data, Recipe $recipe) {
                                switch ($data) {
                                    case 0:
                                        $this->sendRecipeMenu($player, $recipe);
                                        break;
                                    case 1:
                                        $this->sendSetArgs($player, $recipe);
                                        break;
                                    case 2:
                                        $this->sendSetReturns($player, $recipe);
                                        break;
                                }
                            })->addArgs($recipe)->show($player);
                        break;
                    case 6:
                        $this->sendChangeTarget($player, $recipe);
                        break;
                    case 7:
                        $recipe->save(Main::getRecipeManager()->getSaveDir());
                        $this->sendRecipeMenu($player, $recipe, ["@form.recipe.recipeMenu.save.success"]);
                        break;
                    case 8:
                        (new ExportForm)->sendRecipeListByRecipe($player, $recipe);
                        break;
                    case 9:
                        (new MineflowForm)->confirmDelete($player,
                            Language::get("form.recipe.delete.title", [$recipe->getName()]), $recipe->getName(),
                            function (Player $player) use ($recipe) {
                                $manager = Main::getRecipeManager();
                                $recipe->removeTriggerAll();
                                $manager->remove($recipe->getName(), $recipe->getGroup());
                                $this->sendMenu($player, ["@form.delete.success"]);
                            },
                            function (Player $player) use($recipe) {
                                $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                            });
                        break;
                }
            })->addArgs($recipe)->addMessages($messages)->show($player);
    }

    public function sendChangeName(Player $player, Recipe $recipe, ?string $default = null, string $error = null) {
        $form = new CustomForm(Language::get("form.recipe.changeName.title", [$recipe->getName()]));
        $form->setContents([
                new Label("@form.recipe.changeName.content0"),
                new Input("@form.recipe.changeName.content1", "", $default ?? $recipe->getName(), true),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data, Recipe $recipe) {
                if ($data[2]) {
                    $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                    return;
                }

                $manager = Main::getRecipeManager();
                if ($manager->exists($data[1], $recipe->getGroup())) {
                    $newName = $manager->getNotDuplicatedName($data[1], $recipe->getGroup());
                    (new MineflowForm)->confirmRename($player, $data[1], $newName,
                        function (Player $player, string $name) use ($recipe) {
                            $manager = Main::getRecipeManager();
                            $manager->rename($recipe->getName(), $name, $recipe->getGroup());
                            $this->sendRecipeMenu($player, $recipe);
                        },
                        function (Player $player, string $name) use ($recipe) {
                            $this->sendChangeName($player, $recipe, $name, "@form.recipe.exists");
                        });
                    return;
                }
                $manager->rename($recipe->getName(), $data[1], $recipe->getGroup());
                $this->sendRecipeMenu($player, $recipe, ["@form.recipe.changeName.success"]);
            })->addArgs($recipe);
        if ($error) $form->addError($error, 1);
        $form->show($player);
    }

    public function sendTriggerList(Player $player, Recipe $recipe, array $messages = []) {
        $triggers = $recipe->getTriggers();

        $buttons = [new Button("@form.back"), new Button("@trigger.add")];
        foreach ($triggers as $trigger) {
            switch ($trigger->getType()) {
                case Trigger::TYPE_EVENT:
                    $content = "@trigger.type.".$trigger->getType().": @trigger.event.".$trigger->getKey();
                    break;
                default:
                    $content = "@trigger.type.".$trigger->getType().": ".$trigger->getKey();
            }
            $buttons[] = new Button($content);
        }

        (new ListForm(Language::get("form.recipe.triggerList.title", [$recipe->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, Recipe $recipe, array $triggers) {
                if ($data === 0) {
                    $this->sendRecipeMenu($player, $recipe);
                    return;
                }
                if ($data === 1) {
                    (new TriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }
                $data -= 2;

                $trigger = $triggers[$data];
                (new TriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger);
            })->addArgs($recipe, $triggers)->addMessages($messages)->show($player);
    }

    public function sendSetArgs(Player $player, Recipe $recipe, array $messages = []) {
        $contents = [new Toggle("@form.exit")];
        foreach ($recipe->getArguments() as $i => $argument) {
            $contents[] = new Input(Language::get("form.recipe.args", [$i]), "", $argument);
        }
        $contents[] = new Input("@form.recipe.args.add");
        (new CustomForm("@form.recipe.args.set"))
            ->setContents($contents)
            ->onReceive(function (Player $player, array $data, Recipe $recipe) {
                if ($data[0]) {
                    $this->sendRecipeMenu($player, $recipe);
                    return;
                }

                $arguments = [];
                for ($i=1; $i<count($data); $i++) {
                    if ($data[$i] !== "") $arguments[] = $data[$i];
                }
                $recipe->setArguments($arguments);
                $this->sendSetArgs($player, $recipe, ["@form.changed"]);
            })->onClose(function (Player $player, Recipe $recipe) {
                $this->sendRecipeMenu($player, $recipe);
            })->addMessages($messages)->addArgs($recipe)->show($player);
    }

    public function sendSetReturns(Player $player, Recipe $recipe, array $messages = []) {
        $contents = [new Toggle("@form.exit")];
        foreach ($recipe->getReturnValues() as $i => $value) {
            $contents[] = new Input(Language::get("form.recipe.returnValue", [$i]), "", $value);
        }
        $contents[] = new Input("@form.recipe.returnValue.add");
        (new CustomForm("@form.recipe.returnValue.set"))
            ->setContents($contents)
            ->onReceive(function (Player $player, array $data, Recipe $recipe) {
                if ($data[0]) {
                    $this->sendRecipeMenu($player, $recipe);
                    return;
                }

                $returnValues = [];
                for ($i=1; $i<count($data); $i++) {
                    if ($data[$i] !== "") $returnValues[] = $data[$i];
                }
                $recipe->setReturnValues($returnValues);
                $this->sendSetReturns($player, $recipe, ["@form.changed"]);
            })->onClose(function (Player $player, Recipe $recipe) {
                $this->sendRecipeMenu($player, $recipe);
            })->addMessages($messages)->addArgs($recipe)->show($player);
    }

    public function sendChangeTarget(Player $player, Recipe $recipe, array $default = [], array $errors = []) {
        $default1 = $default[1] ?? ($recipe->getTargetType() === Recipe::TARGET_SPECIFIED ? implode(",", $recipe->getTargetOptions()["specified"]) : "");
        $default2 = $default[2] ?? ($recipe->getTargetType() === Recipe::TARGET_RANDOM ? (string)$recipe->getTargetOptions()["random"] : "");
        (new CustomForm(Language::get("form.recipe.changeTarget.title", [$recipe->getName()])))->setContents([
            new Dropdown("@form.recipe.changeTarget.type", [
                Language::get("form.recipe.target.default"),
                Language::get("form.recipe.target.specified"),
                Language::get("form.recipe.target.all"),
                Language::get("form.recipe.target.random"),
                Language::get("form.recipe.target.none"),
            ], $default[0] ?? $recipe->getTargetType()),
            new Input("@form.recipe.changeTarget.name", "@form.recipe.changeTarget.name.placeholder", $default1),
            new Input("@form.recipe.changeTarget.random", "@form.recipe.changeTarget.random.placeholder", $default2),
            new CancelToggle()
        ])->onReceive(function (Player $player, array $data, Recipe $recipe) {
            if ($data[3]) {
                $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                return;
            }

            if ($data[0] === 1 and $data[1] === "") {
                $this->sendChangeTarget($player, $recipe, $data, [["@form.insufficient", 1]]);
                return;
            }
            if ($data[0] === 3 and $data[2] === "") {
                $this->sendChangeTarget($player, $recipe, $data, [["@form.insufficient", 2]]);
                return;
            }

            switch ($data[0]) {
                case 1:
                    $recipe->setTargetSetting((int)$data[0], ["specified" => explode(",", $data[1])]);
                    break;
                case 3:
                    $recipe->setTargetSetting((int)$data[0], ["random" => empty($data[2]) ? 1 : (int)$data[2]]);
                    break;
                default:
                    $recipe->setTargetSetting((int)$data[0]);
                    break;
            }
            $this->sendRecipeMenu($player, $recipe, ["@form.changed"]);
        })->addArgs($recipe)->addErrors($errors)->show($player);
    }
}