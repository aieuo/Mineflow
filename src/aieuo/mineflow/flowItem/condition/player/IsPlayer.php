<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\CheckEntityStateById;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;

class IsPlayer extends CheckEntityStateById {

    protected string $name = "condition.isPlayer.name";
    protected string $detail = "condition.isPlayer.detail";

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_PLAYER, FlowItemCategory::PLAYER, $entityId);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield true;
        return EntityHolder::isPlayer((int)$id);
    }
}
