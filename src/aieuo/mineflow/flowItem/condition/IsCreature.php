<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\entity\Creature;

class IsCreature extends IsActiveEntity {

    protected $id = self::IS_CREATURE;

    protected $name = "condition.isCreature.name";
    protected $detail = "condition.isCreature.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        $entity = EntityHolder::findEntity((int)$id);

        yield true;
        return $entity instanceof Creature;
    }
}