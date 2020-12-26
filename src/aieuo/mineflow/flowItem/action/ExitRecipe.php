<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class ExitRecipe extends FlowItem {

    protected $id = self::EXIT_RECIPE;

    protected $name = "action.exit.name";
    protected $detail = "action.exit.detail";

    protected $category = Category::SCRIPT;

    public function execute(Recipe $origin): \Generator {
        $origin->exit();
        yield true;
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