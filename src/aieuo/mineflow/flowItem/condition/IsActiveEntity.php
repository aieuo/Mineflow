<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;

class IsActiveEntity extends CheckEntityStateById {

    protected string $name = "condition.isActiveEntity.name";
    protected string $detail = "condition.isActiveEntity.detail";

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_ACTIVE_ENTITY, entityId: $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield true;
        return EntityHolder::isActive((int)$id);
    }
}