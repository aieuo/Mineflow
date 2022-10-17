<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class IsSprinting extends CheckPlayerState {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_SPRINTING, player: $player);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->isSprinting();
    }
}
