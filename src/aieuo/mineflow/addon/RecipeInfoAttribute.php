<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

class RecipeInfoAttribute {

    public function __construct(
        private string $actionId,
        private string $category,
        private string $recipePath,
    ) {
    }

    public function getActionId(): string {
        return $this->actionId;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getRecipePath(): string {
        return $this->recipePath;
    }
}
