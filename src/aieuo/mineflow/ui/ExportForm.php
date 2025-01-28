<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use pocketmine\player\Player;

class ExportForm {

    public function sendRecipeListByRecipe(Player $player, Recipe $recipe): void {
        $recipes = Mineflow::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe);
        $this->sendRecipeList($player, $recipes);
    }

    public function sendRecipeList(Player $player, array $recipes, array $messages = []): void {
        $recipes = array_values($recipes);

        $buttons = [];
        foreach ($recipes as $i => $recipe) {
            $buttons[] = new Button($recipe->getGroup()."/".$recipe->getName(), fn() => $this->sendRecipeMenu($player, array_values($recipes), $i));
        }

        (new ListForm("@form.export.recipeList.title"))
            ->addButton(new Button("@form.export.execution", fn() => $this->sendExportMenu($player, $recipes)))
            ->addButton(new Button("@form.add", fn() => $this->sendSelectRecipe($player, $recipes)))
            ->addButtons($buttons)
            ->addMessages($messages)
            ->show($player);
    }

    public function sendSelectRecipe(Player $player, array $recipes): void {
        (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title",
            function (Recipe $recipe) use ($player, $recipes) {
                $recipes = array_merge($recipes, Mineflow::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe));
                $this->sendRecipeList($player, $recipes, ["@form.added"]);
            },
            fn() => $this->sendRecipeList($player, $recipes, ["@form.cancelled"]),
            default: [1 => $recipes[0]?->getGroup()]
        );
    }

    public function sendRecipeMenu(Player $player, array $recipes, int $index): void {
        $recipe = $recipes[$index];
        (new ListForm($recipe->getName()))
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, array $recipes, int $index) {
                if ($data === 0) {
                    $this->sendRecipeList($player, $recipes);
                } else {
                    unset($recipes[$index]);
                    $this->sendRecipeList($player, $recipes, ["@form.deleted"]);
                }
            })->addArgs($recipes, $index)->show($player);
    }

    /**
     * @param Player $player
     * @param Recipe[] $recipes
     * @param array<string|int> $default
     * @param array<string|int>[] $errors
     */
    public function sendExportMenu(Player $player, array $recipes, array $default = [], array $errors = []): void {
        if (empty($recipes)) {
            $this->sendRecipeList($player, $recipes, ["@form.export.empty"]);
            return;
        }

        (new CustomForm("@mineflow.export"))
            ->setContents([
                new Input("@form.export.name", "", $default[0] ?? "", true),
                new Input("@form.export.author", "", $default[1] ?? $player->getName(), true),
                new Input("@form.export.detail", "", $default[2] ?? ""),
                new Toggle("@form.export.includeConfig", true),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, array $recipes) {
                if ($data[4]) {
                    $this->sendRecipeList($player, $recipes, ["@form.cancelled"]);
                    return;
                }

                [$name, $author, $detail] = $data;

                /** @var array<string|int>[] $errors */
                $errors = [];
                if (!Utils::isValidFileName($name)) $errors = ["@form.recipe.invalidName", 0];

                if (!empty($errors)) {
                    $this->sendExportMenu($player, $recipes, $data, $errors);
                    return;
                }

                $pack = new RecipePack($name, $author, $detail, $recipes, null, null, $data[3] ? null : []);
                $pack->export(Main::getInstance()->getDataFolder()."/exports/");

                $player->sendMessage(Language::get("form.export.success", [$name.".json"]));
            })->addErrors($errors)->addArgs($recipes)->show($player);
    }

}