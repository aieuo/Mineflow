<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsSwimming extends IsFlying {

    protected string $id = self::IS_SWIMMING;

    protected string $name = "condition.isSwimming.name";
    protected string $detail = "condition.isSwimming.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isSwimming();
    }
}
