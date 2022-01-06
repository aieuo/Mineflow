<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\entity\Living;

class IsCreature extends CheckEntityStateById {

    protected string $name = "condition.isCreature.name";
    protected string $detail = "condition.isCreature.detail";

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_CREATURE, entityId: $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        $entity = EntityHolder::findEntity((int)$id);

        yield true;
        return $entity instanceof Living;
    }
}