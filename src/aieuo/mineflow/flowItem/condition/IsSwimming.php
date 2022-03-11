<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsSwimming extends CheckPlayerState {

    protected string $name = "condition.isSwimming.name";
    protected string $detail = "condition.isSwimming.detail";

    public function __construct(string $player = "") {
        parent::__construct(self::IS_SWIMMING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isSwimming();
    }
}
