<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class IsFlying extends CheckPlayerState {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_FLYING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield Await::ALL;
        return $player->isFlying();
    }
}
