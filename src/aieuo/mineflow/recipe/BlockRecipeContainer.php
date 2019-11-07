<?php

namespace aieuo\mineflow\recipe;

class BlockRecipeContainer extends RecipeContainer {
    public function jsonSerialize(): array {
        $recipes = [];
        foreach ($this->getAllRecipe() as $recipe) {
            $name = $recipe->getName();
            if ($name === null) {
                $recipes[] = $recipe;
            } else {
                $recipes[$name] = $recipe;
            }
        }
        return [
            "name" => $this->getName(),
            "recipes" => $recipes,
        ];
    }
}