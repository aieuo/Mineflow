<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\recipe\Recipe;

class IsPlayer extends IsActiveEntity {

    protected $id = self::IS_PLAYER;

    protected $name = "condition.isPlayer.name";
    protected $detail = "condition.isPlayer.detail";

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $id = $origin->replaceVariables($this->getEntityId());
        if (!is_numeric($id)) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("flowItem.error.notNumber")]), $target);
            return null;
        }

        $entity = EntityHolder::findEntity((int)$id);
        return $entity instanceof Player;
    }
}