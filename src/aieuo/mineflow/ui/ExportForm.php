<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class ExportForm {

    public function sendRecipeListByRecipe(Player $player, Recipe $recipe) {
        $recipes = Main::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe);
        $this->sendRecipeList($player, $recipes);
    }

    public function sendRecipeList(Player $player, array $recipes, array $messages = []) {
        $buttons = [new Button("@form.export.execution"), new Button("@form.add")];
        foreach ($recipes as $recipe) {
            $buttons[] = new Button($recipe->getGroup()."/".$recipe->getName());
        }
        (new ListForm("@form.export.recipeList.title"))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, array $recipes) {
                if ($data === 0) {
                    $this->sendExportMenu($player, $recipes);
                    return;
                }
                if ($data === 1) {
                    (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title", function (Player $player, Recipe $recipe) use ($recipes) {
                        $recipes = array_merge($recipes, Main::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe));
                        $this->sendRecipeList($player, $recipes, ["@form.added"]);
                    });
                    return;
                }
                $data -= 2;

                $this->sendRecipeMenu($player, $recipes, $data);
            })->addMessages($messages)->addArgs($recipes)->show($player);
    }

    public function sendRecipeMenu(Player $player, array $recipes, int $index) {
        $recipe = $recipes[$index];
        (new ListForm($recipe->getName()))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, array $recipes, int $index) {
                switch ($data) {
                    case 0:
                        $this->sendRecipeList($player, $recipes);
                        return;
                    default:
                        unset($recipes[$index]);

                        $recipes = array_values($recipes);
                        $this->sendRecipeList($player, $recipes, ["@form.delete.success"]);
                        return;
                }
            })->addArgs($recipes, $index)->show($player);
    }

    public function sendExportMenu(Player $player, array $recipes, array $default = [], array $errors = []) {
        if (empty($recipes)) {
            $this->sendRecipeList($player, $recipes, ["@form.export.empty"]);
            return;
        }

        (new CustomForm("@mineflow.export"))
            ->setContents([
                new Input("@form.export.name", "", $default[0] ?? ""),
                new Input("@form.export.author", "", $default[1] ?? $player->getName()),
                new Input("@form.export.detail", "", $default[2] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data, array $recipes) {
                if ($data[3]) {
                    $this->sendRecipeList($player, $recipes, ["@form.canceled"]);
                    return;
                }

                $name = $data[0];
                $author = $data[1];
                $detail = $data[2];

                $errors = [];
                if ($name === "") $errors[] = ["@form.insufficient", 0];
                if (preg_match("#[.¥/:?<>|*\"]#", preg_quote($name))) $errors = ["@form.recipe.invalidName", 0];
                if ($author === "") $errors[] = ["@form.insufficient", 1];

                if (!empty($errors)) {
                    $this->sendExportMenu($player, $recipes, $data, $errors);
                    return;
                }

                $pack = new RecipePack($name, $author, $detail, $recipes);
                $pack->export(Main::getInstance()->getDataFolder()."/exports/");

                $player->sendMessage(Language::get("form.export.success"));
            })->addErrors($errors)->addArgs($recipes)->show($player);
    }

}