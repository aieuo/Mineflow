<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class IsFlying extends CheckPlayerState {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_FLYING, player: $player);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->isFlying();
    }
}