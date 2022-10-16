<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use SOFe\AwaitGenerator\Await;

class IsActiveEntity extends CheckEntityStateById {

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_ACTIVE_ENTITY, entityId: $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield Await::ALL;
        return EntityHolder::isActive((int)$id);
    }
}
