<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;

class IsPlayer extends IsActiveEntity {

    protected $id = self::IS_PLAYER;

    protected $name = "condition.isPlayer.name";
    protected $detail = "condition.isPlayer.detail";

    protected $category = Category::PLAYER;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield true;
        return EntityHolder::isPlayer((int)$id);
    }
}