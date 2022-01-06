<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsFlying extends CheckPlayerState {

    protected string $name = "condition.isFlying.name";
    protected string $detail = "condition.isFlying.detail";

    public function __construct(string $player = "") {
        parent::__construct(self::IS_FLYING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isFlying();
    }
}
