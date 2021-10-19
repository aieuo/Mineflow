<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;

class IsPlayer extends IsActiveEntity {

    protected string $id = self::IS_PLAYER;

    protected string $name = "condition.isPlayer.name";
    protected string $detail = "condition.isPlayer.detail";

    protected string $category = Category::PLAYER;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        FlowItemExexutor::CONTINUE;
        return EntityHolder::isPlayer((int)$id);
    }
}