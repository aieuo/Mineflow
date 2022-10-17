<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class IsCreature extends CheckEntityStateById {

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_CREATURE, entityId: $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $this->getInt($source->replaceVariables($this->getEntityId()));
        $entity = EntityHolder::findEntity($id);

        yield Await::ALL;
        return $entity instanceof Living;
    }
}
