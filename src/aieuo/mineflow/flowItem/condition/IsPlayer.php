<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;

class IsPlayer extends IsActiveEntity {

    protected $id = self::IS_PLAYER;

    protected $name = "condition.isPlayer.name";
    protected $detail = "condition.isPlayer.detail";

    protected $category = Category::PLAYER;

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield true;
        return EntityHolder::isPlayer((int)$id);
    }
}