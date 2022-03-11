<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsSprinting extends CheckPlayerState {

    protected string $name = "condition.isSprinting.name";
    protected string $detail = "condition.isSprinting.detail";

    public function __construct(string $player = "") {
        parent::__construct(self::IS_SPRINTING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isSprinting();
    }
}
