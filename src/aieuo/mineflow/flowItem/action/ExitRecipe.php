<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class ExitRecipe extends FlowItem {

    protected $id = self::EXIT_RECIPE;

    protected $name = "action.exit.name";
    protected $detail = "action.exit.detail";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin) {
        $origin->exit();
        yield true;
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}