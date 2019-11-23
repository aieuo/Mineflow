<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\recipe\Recipe;

class IsPlayer extends IsActiveEntity {

    protected $id = self::IS_PLAYER;

    protected $name = "@condition.isPlayer.name";
    protected $description = "@condition.isPlayer.description";
    protected $detail = "condition.isPlayer.detail";

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            if ($target instanceof Player) $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            else Server::getInstance()->getLogger()->info(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }


        $id = $this->getEntityId();
        if ($origin instanceof Recipe) {
            $id = $origin->replaceVariables($id);
        }
        if (!is_numeric($id)) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        }

        $entity = EntityHolder::getInstance()->findEntity((int)$id);
        return $entity instanceof Player;
    }
}