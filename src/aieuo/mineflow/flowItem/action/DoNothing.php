<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class DoNothing extends Action {

    protected $id = self::DO_NOTHING;

    protected $name = "action.doNothing.name";
    protected $detail = "action.doNothing.detail";

    protected $category = Category::COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin) {
        yield true;
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): Action {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}