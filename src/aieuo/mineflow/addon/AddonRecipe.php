<?php
declare(strict_types=1);

namespace aieuo\mineflow\addon;

use aieuo\mineflow\recipe\Recipe;

class AddonRecipe extends Recipe {

    public function __construct(string $name, string $group = "", string $author = "", string $pluginVersion = null) {
        parent::__construct($name, $group, $author, $pluginVersion);
        $this->setReadonly(true);
    }

    public function save(string $dir): void {
    }

    public function unlink(string $dir): void {
    }
}