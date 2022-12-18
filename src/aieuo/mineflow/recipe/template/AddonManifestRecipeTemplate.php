<?php

declare(strict_types=1);

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\flowItem\action\script\ActionGroup;
use aieuo\mineflow\flowItem\action\variable\AddListVariable;
use aieuo\mineflow\flowItem\action\variable\AddMapVariable;
use aieuo\mineflow\flowItem\action\variable\CreateListVariable;
use aieuo\mineflow\flowItem\action\variable\CreateMapVariable;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;

class AddonManifestRecipeTemplate extends RecipeTemplate {

    private string $addonId = "";

    public static function getName(): string {
        return Language::get("recipe.template.addon.manifest");
    }

    public function getSettingFormPart(): RecipeTemplateSettingFormPart {
        return new RecipeTemplateSettingFormPart(
            [
                new ExampleInput("@recipe.template.addon.manifest.id", "aieuo", $this->addonId, true, result: $this->addonId),
            ],
            messages: $this->getRecipeName() === "_manifest" ? [] : [Language::get("recipe.template.addon.manifest.name.change")]
        );
    }

    public function build(): Recipe {
        $recipe = new Recipe("_manifest", $this->getRecipeGroup());

        $recipe->addAction(new CreateMapVariable("manifest", "", "", true));
        $recipe->addAction(new CreateListVariable("recipes", "", true));
        $recipe->addAction(new AddMapVariable("manifest", "recipes", "{recipes}", true));
        $recipe->addAction(new AddMapVariable("manifest", "id", $this->addonId, true));
        $group = new ActionGroup();
        $group->addAction(new CreateMapVariable("recipe_data", "", "", true));
        $group->addAction(new AddListVariable("recipes", "{recipe_data}", true));
        $group->addAction(new AddMapVariable("recipe_data", "path", "§bEDIT HERE: recipe name§f", true));
        $group->addAction(new AddMapVariable("recipe_data", "id", "§bEDIT HERE: action id§f", true));
        $group->addAction(new AddMapVariable("recipe_data", "category", "§bEDIT HERE: action category§f", true));
        $recipe->addAction($group);
        return $recipe;
    }
}
