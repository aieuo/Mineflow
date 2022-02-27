<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsGliding extends IsFlying {

    protected string $id = self::IS_GLIDING;

    protected string $name = "condition.isGliding.name";
    protected string $detail = "condition.isGliding.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isGliding();
    }
}
