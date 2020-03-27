<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class DoNothing extends Action {

    protected $id = self::DO_NOTHING;

    protected $name = "action.doNothing.name";
    protected $detail = "action.doNothing.detail";

    protected $category = Categories::CATEGORY_ACTION_COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(Recipe $origin): bool {
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