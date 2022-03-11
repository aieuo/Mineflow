<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;

class IsActiveEntityVariable extends CheckEntityState {

    protected string $name = "condition.isActiveEntityVariable.name";
    protected string $detail = "condition.isActiveEntityVariable.detail";

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_ACTIVE_ENTITY_VARIABLE, FlowItemCategory::ENTITY, $entity);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }
}