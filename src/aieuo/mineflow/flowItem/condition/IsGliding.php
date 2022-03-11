<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IsGliding extends CheckPlayerState {

    protected string $name = "condition.isGliding.name";
    protected string $detail = "condition.isGliding.detail";

    public function __construct(string $player = "") {
        parent::__construct(self::IS_GLIDING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->isGliding();
    }
}
