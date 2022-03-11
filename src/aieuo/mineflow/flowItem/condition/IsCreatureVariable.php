<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;

class IsCreatureVariable extends CheckEntityState {

    protected string $name = "condition.isCreatureVariable.name";
    protected string $detail = "condition.isCreatureVariable.detail";

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_CREATURE_VARIABLE, entity: $entity);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity instanceof Living;
    }
}