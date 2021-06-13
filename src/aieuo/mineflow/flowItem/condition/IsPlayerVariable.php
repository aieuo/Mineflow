<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;
use pocketmine\Player;

class IsPlayerVariable extends IsActiveEntityVariable {

    protected string $id = self::IS_PLAYER_VARIABLE;

    protected string $name = "condition.isPlayerVariable.name";
    protected string $detail = "condition.isPlayerVariable.detail";

    protected string $category = Category::PLAYER;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity instanceof Player;
    }
}