<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

class AddonManifest {
    /**
     * @param string $addonId
     * @param array $recipeInfos
     */
    public function __construct(
        private string $addonId,
        private array $recipeInfos = [],
    ) {
    }

    public function getAddonId(): string {
        return $this->addonId;
    }

    public function getRecipeInfos(): array {
        return $this->recipeInfos;
    }
}
