<?php

declare(strict_types=1);

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\flowItem\action\script\ActionGroup;
use aieuo\mineflow\flowItem\action\variable\AddListVariable;
use aieuo\mineflow\flowItem\action\variable\AddMapVariable;
use aieuo\mineflow\flowItem\action\variable\CreateListVariable;
use aieuo\mineflow\flowItem\action\variable\CreateMapVariable;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;

class AddonManifestRecipeTemplate extends RecipeTemplate {

    private string $path = "";
    private string $id = "";
    private int $category = 0;

    private string $name = "";

    private string $description = "";

    public static function getName(): string {
        return Language::get("recipe.template.addon.manifest");
    }

    public function getSettingFormPart(): RecipeTemplateSettingFormPart {
        return new RecipeTemplateSettingFormPart(
            [
                new ExampleInput("@recipe.template.addon_manifest.path", $this->getRecipeGroup()."/main", required: true, result: $this->path),
                new ExampleInput("@recipe.template.addon_manifest.id", "aieuo_addon_main", required: true, result: $this->id),
                new Dropdown(
                    "@recipe.template.addon_manifest.category",
                    array_map(fn($category) => FlowItemCategory::name($category), FlowItemCategory::all()),
                    result: $this->category
                ),
                new ExampleInput("@recipe.template.addon_manifest.name", "aieuo", required: true, result: $this->name),
                new ExampleInput("@recipe.template.addon_manifest.description", "aieuo{%0}aieuo{%1}", required: true, result: $this->description),
            ],
            messages: $this->getRecipeName() === "_manifest" ? [] : [Language::get("recipe.template.addon.manifest.name.change")]
        );
    }

    public function build(): Recipe {
        $recipe = Mineflow::getRecipeManager()->get("_manifest", $this->getRecipeGroup());
        if ($recipe === null) {
            $recipe = new Recipe("_manifest", $this->getRecipeGroup());
            $recipe->addAction(new CreateMapVariable("manifest", "", "", true));
            $recipe->addAction(new CreateListVariable("recipes", "", true));
            $recipe->addAction(new AddMapVariable("manifest", "recipes", "{recipes}", true));

            $recipe->setReturnValues(["manifest"]);
        }

        $group = new ActionGroup();
        $group->getActions()->addItem(new CreateMapVariable("recipe_data", "", "", true));
        $group->getActions()->addItem(new AddListVariable("recipes", "{recipe_data}", true));
        $group->getActions()->addItem(new AddMapVariable("recipe_data", "path", $this->path, true));
        $group->getActions()->addItem(new AddMapVariable("recipe_data", "id", $this->id, true));
        $group->getActions()->addItem(new AddMapVariable("recipe_data", "category", FlowItemCategory::all()[$this->category], true));
        $group->getActions()->addItem(new AddMapVariable("recipe_data", "name", $this->name, true));
        $group->getActions()->addItem(new AddMapVariable("recipe_data", "description", $this->description, true));
        $recipe->addAction($group);
        return $recipe;
    }
}