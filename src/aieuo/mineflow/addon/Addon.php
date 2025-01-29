<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use function array_merge;

class Addon {
    /**
     * @param string $name
     * @param string $author
     * @param string $version
     * @param Recipe[] $loadedRecipes
     * @param RecipePack $pack
     * @param AddonManifest|null $manifest
     * @param string $path
     */
    public function __construct(
        private string $name,
        private string $author,
        private string $version,
        private array $loadedRecipes,
        private RecipePack $pack,
        private ?AddonManifest $manifest,
        private string $path,
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getVersion(): string {
        return $this->version;
    }

    public function getLoadedRecipes(): array {
        return $this->loadedRecipes;
    }

    public function setLoadedRecipes(array $loadedRecipes): void {
        $this->loadedRecipes = $loadedRecipes;
    }

    public function getPack(): RecipePack {
        return $this->pack;
    }

    public function getManifest(): ?AddonManifest {
        return $this->manifest;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getDependencies(): array {
        $dependencies = [];
        foreach ($this->pack->getRecipes() as $recipe) {
            $dependencies = array_merge($dependencies, $recipe?->getLastAddonDependencies() ?? []);
        }

        return array_values(array_unique($dependencies));
    }
}