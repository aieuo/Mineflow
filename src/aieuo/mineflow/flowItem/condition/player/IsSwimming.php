<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class IsSwimming extends CheckPlayerState {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_SWIMMING, player: $player);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->isSwimming();
    }
}