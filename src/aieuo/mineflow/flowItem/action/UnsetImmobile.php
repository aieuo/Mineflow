<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class UnsetImmobile extends Action {

    protected $id = self::UNSET_IMMOBILE;

    protected $name = "action.unsetImmobile.name";
    protected $detail = "action.unsetImmobile.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $target->setImmobile(false);
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): ?Action {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}