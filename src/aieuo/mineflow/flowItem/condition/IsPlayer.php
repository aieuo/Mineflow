<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\recipe\Recipe;

class IsPlayer extends IsActiveEntity {

    protected $id = self::IS_PLAYER;

    protected $name = "condition.isPlayer.name";
    protected $detail = "condition.isPlayer.detail";

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        return EntityHolder::isPlayer((int)$id);
    }
}