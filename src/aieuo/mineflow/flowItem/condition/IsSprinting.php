<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsSprinting extends IsFlying {

    protected string $id = self::IS_SPRINTING;

    protected string $name = "condition.isSprinting.name";
    protected string $detail = "condition.isSprinting.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isSprinting();
    }
}
