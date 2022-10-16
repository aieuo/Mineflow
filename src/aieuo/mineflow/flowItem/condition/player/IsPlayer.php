<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\CheckEntityStateById;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use SOFe\AwaitGenerator\Await;

class IsPlayer extends CheckEntityStateById {

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_PLAYER, FlowItemCategory::PLAYER, $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield Await::ALL;
        return EntityHolder::isPlayer((int)$id);
    }
}
