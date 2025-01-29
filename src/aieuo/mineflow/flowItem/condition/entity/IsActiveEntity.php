<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class IsActiveEntity extends CheckEntityStateById {

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_ACTIVE_ENTITY, entityId: $entityId);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->getEntityId()->getInt($source);

        yield Await::ALL;
        return EntityHolder::isActive($id);
    }
}