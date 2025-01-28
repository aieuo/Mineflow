<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

use aieuo\mineflow\variable\MapVariable;

class AddonManifest {
    /**
     * @param RecipeInfoAttribute[] $recipeInfos
     * @param MapVariable $manifestVariable
     */
    public function __construct(
        private array $recipeInfos,
        private MapVariable $manifestVariable,
    ) {
    }

    public function getRecipeInfos(): array {
        return $this->recipeInfos;
    }

    public function getVariable(): MapVariable {
        return $this->manifestVariable;
    }
}