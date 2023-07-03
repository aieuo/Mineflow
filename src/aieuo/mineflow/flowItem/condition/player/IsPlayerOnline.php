<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class IsPlayerOnline extends SimpleCondition {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_PLAYER_ONLINE, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getPlayer($source);

        yield Await::ALL;
        return $player->isOnline();
    }
}
