<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class IsGliding extends CheckPlayerState {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_GLIDING, player: $player);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->isGliding();
    }
}