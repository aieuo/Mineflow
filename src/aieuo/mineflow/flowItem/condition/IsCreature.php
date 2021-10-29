<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\entity\Creature;

class IsCreature extends IsActiveEntity {

    protected string $id = self::IS_CREATURE;

    protected string $name = "condition.isCreature.name";
    protected string $detail = "condition.isCreature.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $this->getInt($source->replaceVariables($this->getEntityId()));
        $entity = EntityHolder::findEntity($id);

        yield FlowItemExecutor::CONTINUE;
        return $entity instanceof Creature;
    }
}