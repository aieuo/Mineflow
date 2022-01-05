<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;

class IsCreatureVariable extends IsActiveEntityVariable {

    protected string $id = self::IS_CREATURE_VARIABLE;

    protected string $name = "condition.isCreatureVariable.name";
    protected string $detail = "condition.isCreatureVariable.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity instanceof Living;
    }
}