<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class SetImmobile extends Action {

    protected $id = self::SET_IMMOBILE;

    protected $name = "action.setImmobile.name";
    protected $detail = "action.setImmobile.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $target->setImmobile(true);
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