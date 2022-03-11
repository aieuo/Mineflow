<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;

class IsPlayerVariable extends CheckEntityState {

    protected string $name = "condition.isPlayerVariable.name";
    protected string $detail = "condition.isPlayerVariable.detail";

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_PLAYER_VARIABLE, FlowItemCategory::PLAYER, $entity);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity instanceof Player;
    }
}
