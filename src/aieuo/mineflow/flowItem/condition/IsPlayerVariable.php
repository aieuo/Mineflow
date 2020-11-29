<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\Player;

class IsPlayerVariable extends IsActiveEntityVariable {

    protected $id = self::IS_PLAYER_VARIABLE;

    protected $name = "condition.isPlayerVariable.name";
    protected $detail = "condition.isPlayerVariable.detail";

    protected $category = Category::PLAYER;

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity instanceof Player;
    }
}