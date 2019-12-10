<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class UnsetImmobile extends Process {

    protected $id = self::UNSET_IMMOBILE;

    protected $name = "@action.unsetImmobile.name";
    protected $description = "@action.unsetImmobile.description";
    protected $detail = "@action.unsetImmobile.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Entity)) return null;
        $target->setImmobile(false);
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function parseFromSaveData(array $content): ?Process {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}