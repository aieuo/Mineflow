<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\FormAPI\element\Button;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\variable\DefaultVariables;

class RecipeForm {

    public function sendMenu(Player $player, array $messages = []) {
        (new ListForm("@mineflow.recipe"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.add"),
                new Button("@form.edit"),
                new Button("@form.recipe.menu.recipeList"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        $this->sendAddRecipe($player);
                        break;
                    case 1:
                        $this->sendSelectRecipe($player);
                        break;
                    case 2:
                        $this->sendRecipeList($player);
                        break;
                    default:
                        (new HomeForm)->sendMenu($player);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendAddRecipe(Player $player, string $default = "", string $error = null) {
        $manager = Main::getInstance()->getRecipeManager();
        $name = $manager->getNotDuplicatedName("Recipe");

        $form = new CustomForm("@form.recipe.addRecipe.title");
        $form->setContents([
                new Input("@form.recipe.recipeName", $name, $default),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data, string $defaultName) {
                if ($data === null) return;
                if ($data[1]) {
                    $this->sendMenu($player);
                    return;
                }

                $manager = Main::getInstance()->getRecipeManager();
                $name = $data[0] === "" ? $defaultName : $data[0];
                if ($manager->exists($name)) {
                    $newName = $manager->getNotDuplicatedName($name);
                    (new HomeForm)->sendConfirmRename($player, $name, $newName, function (bool $result, string $name, string $newName) use ($player) {
                        if ($result) {
                            $manager = Main::getInstance()->getRecipeManager();
                            $recipe = new Recipe($newName);
                            $manager->add($recipe);
                            Session::getSession($player)->set("recipe_menu_prev", [$this, "sendMenu"]);
                            $this->sendRecipeMenu($player, $recipe);
                        } else {
                            $this->sendAddRecipe($player, $name, Language::get("form.recipe.exists", [$name]));
                        }
                    });
                    return;
                }

                $recipe = new Recipe($name);
                $manager->add($recipe);
                Session::getSession($player)->set("recipe_menu_prev", [$this, "sendSelectRecipe"]);
                $this->sendRecipeMenu($player, $recipe);
            })->addArgs($name);
        if ($error) $form->addError($error, 0);
        $form->show($player);
    }

    public function sendSelectRecipe(Player $player, string $default = "", string $error = null) {
        $form = new CustomForm("@form.recipe.select.title");
        $form->setContents([
                new Input("@form.recipe.recipeName", "", $default),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data) {
                if ($data === null) return;

                if ($data[1]) {
                    $this->sendMenu($player);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSelectRecipe($player, "", "@form.insufficient");
                    return;
                }

                $manager = Main::getInstance()->getRecipeManager();
                $name = $data[0];
                if (!$manager->exists($name)) {
                    $this->sendSelectRecipe($player, $name, "@form.recipe.select.notfound");
                    return;
                }

                $recipe = $manager->get($name);
                Session::getSession($player)->set("recipe_menu_prev", [$this, "sendSelectRecipe"]);
                $this->sendRecipeMenu($player, $recipe);
            });
        if ($error) $form->addError($error, 0);
        $form->show($player);
    }

    public function sendRecipeList(Player $player) {
        $manager = Main::getInstance()->getRecipeManager();
        $recipes = $manager->getAll();
        $buttons = [new Button("@form.back")];
        foreach ($recipes as $recipe) {
            $buttons[] = new Button($recipe->getName());
        }

        (new ListForm("@form.recipe.recipeList.title"))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, array $recipes) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->sendMenu($player);
                    return;
                }
                $data --;

                $recipe = $recipes[$data];
                Session::getSession($player)->set("recipe_menu_prev", [$this, "sendRecipeList"]);
                $this->sendRecipeMenu($player, $recipe);
            })->addArgs(array_values($recipes))->show($player);
    }

    public function sendRecipeMenu(Player $player, Recipe $recipe, array $messages = []) {
        $detail = trim($recipe->getDetail());
        (new ListForm(Language::get("form.recipe.recipeMenu.title", [$recipe->getName()])))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@action.edit"),
                new Button("@form.recipe.recipeMenu.changeName"),
                new Button("@form.recipe.recipeMenu.execute"),
                new Button("@form.recipe.recipeMenu.setTrigger"),
                new Button("@form.recipe.recipeMenu.save"),
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        Session::getSession($player)->set("parents", []);
                        (new ActionContainerForm)->sendActionList($player, $recipe);
                        break;
                    case 1:
                        $this->sendChangeName($player, $recipe);
                        break;
                    case 2:
                        $variables = array_merge(DefaultVariables::getServerVariables(), DefaultVariables::getPlayerVariables($player));
                        $recipe->executeAllTargets($player, $variables);
                        break;
                    case 3:
                        $this->sendTriggerList($player, $recipe);
                        break;
                    case 4:
                        $recipe->save(Main::getInstance()->getRecipeManager()->getSaveDir());
                        $this->sendRecipeMenu($player, $recipe, ["@form.recipe.recipeMenu.save.success"]);
                        break;
                    case 5:
                        $this->sendConfirmDelete($player, $recipe);
                        break;
                    default:
                        $prev = Session::getSession($player)->get("recipe_menu_prev");
                        if (is_callable($prev)) call_user_func_array($prev, [$player]);
                        else $this->sendMenu($player);
                        break;
                }
            })->addArgs($recipe)->addMessages($messages)->show($player);
    }

    public function sendChangeName(Player $player, Recipe $recipe, ?string $default = null, string $error = null) {
        $form = new CustomForm(Language::get("form.recipe.changeName.title", [$recipe->getName()]));
        $form->setContents([
                new Label("@form.recipe.changeName.content0"),
                new Input("@form.recipe.changeName.content1", "", $default ?? $recipe->getName()),
                new Toggle("@form.cancelAndBack")
            ])->onRecive(function (Player $player, ?array $data, Recipe $recipe) {
                if ($data === null) return;

                if ($data[2]) {
                    $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                    return;
                }

                if ($data[1] === "") {
                    $this->sendChangeName($player, $recipe, $data[1], "@form.insufficient");
                    return;
                }

                $manager = Main::getInstance()->getRecipeManager();
                if ($manager->exists($data[1])) {
                    $newName = $manager->getNotDuplicatedName($data[1]);
                    (new HomeForm)->sendConfirmRename($player, $data[1], $newName, function (bool $result, string $name, string $newName) use ($player, $recipe, $manager) {
                        if ($result) {
                            $manager->rename($recipe->getName(), $newName);
                            $this->sendRecipeMenu($player, $recipe);
                        } else {
                            $this->sendChangeName($player, $recipe, $name, "@form.recipe.exists");
                        }
                    });
                    return;
                }
                $manager->rename($recipe->getName(), $data[1]);
                $this->sendRecipeMenu($player, $recipe, ["@form.recipe.changeName.success"]);
            })->addArgs($recipe);
        if ($error) $form->addError($error, 1);
        $form->show($player);
    }

    public function sendTriggerList(Player $player, Recipe $recipe, array $messages = []) {
        $triggers = $recipe->getTriggers();

        $buttons = [new Button("@form.back"), new Button("@trigger.add")];
        foreach ($triggers as $trigger) {
            switch ($trigger[0]) {
                case TriggerManager::TRIGGER_EVENT:
                    $content = "@trigger.type.".$trigger[0].": @trigger.event.".$trigger[1];
                    break;
                default:
                    $content = "@trigger.type.".$trigger[0].": ".$trigger[1];
            }
            $buttons[] = new Button($content);
        }

        (new ListForm(Language::get("form.recipe.triggerList.title", [$recipe->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $triggers) {
                if ($data === null) return;

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

    public function sendConfirmDelete(Player $player, Recipe $recipe) {
        (new ModalForm(Language::get("form.recipe.delete.title", [$recipe->getName()])))
            ->setContent(Language::get("form.delete.confirm", [$recipe->getName()]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, Recipe $recipe) {
                if ($data === null) return;

                if ($data) {
                    $manager = Main::getInstance()->getRecipeManager();
                    $manager->remove($recipe->getName());
                    $this->sendMenu($player, ["@form.delete.success"]);
                } else {
                    $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                }
            })->addArgs($recipe)->show($player);
    }
}