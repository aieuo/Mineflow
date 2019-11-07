<?php

namespace aieuo\mineflow\manager;

use pocketmine\math\Vector3;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\BlockRecipeContainer;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Logger;

class BlockRecipeManager extends RecipeManager {

    public function __construct(Main $owner) {
        parent::__construct($owner, "blocks");
    }

    public function loadRecipes(): void {
        $files = glob($this->getSaveDir()."/*.json");
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data === null) continue;
            if (!isset($data["name"]) or !isset($data["recipes"])) continue;

            $recipes = [];
            $name = $data["name"];
            $load = true;
            foreach ($data["recipes"] as $recipeName => $rdata) {
                $recipe = (new Recipe($recipeName))->parseFromSaveData($rdata["actions"]);
                if ($recipe === null) {
                    Logger::warning(Language::get("recipe.load.faild", [$name, $recipeName]));
                    $load = false;
                    break;
                }

                $recipe->setTarget(
                    $rdata["targetType"] ?? Recipe::TARGET_DEFAULT,
                    $rdata["targetOptions"] ?? []
                );
                var_dump($recipe, $recipe->getDetail());
                $recipes[] = $recipe;
            }
            if ($load) $this->set($name, new BlockRecipeContainer($name, $recipes));
        }
    }

    public function addRecipe(string $name, Recipe $recipe) {
        if (!$this->exists($name)) $this->set($name, new BlockRecipeContainer($name));
        $this->get($name)->addRecipe($recipe);
    }

    public function getPositionAsString(Vector3 $block): string {
        return $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
    }
}