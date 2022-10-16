<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class IsActiveEntityVariable extends CheckEntityState {

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_ACTIVE_ENTITY_VARIABLE, FlowItemCategory::ENTITY, $entity);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield Await::ALL;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }
}
