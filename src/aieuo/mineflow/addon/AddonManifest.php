<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

use aieuo\mineflow\variable\MapVariable;

class AddonManifest {
    /**
     * @param string $addonId
     * @param array $recipeInfos
     * @param MapVariable $manifestVariable
     */
    public function __construct(
        private string $addonId,
        private array $recipeInfos,
        private MapVariable $manifestVariable,
    ) {
    }

    public function getAddonId(): string {
        return $this->addonId;
    }

    public function getRecipeInfos(): array {
        return $this->recipeInfos;
    }

    public function getVariable(): MapVariable {
        return $this->manifestVariable;
    }
}
