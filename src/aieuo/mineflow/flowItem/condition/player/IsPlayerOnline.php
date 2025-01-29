<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class IsPlayerOnline extends SimpleCondition {

    public function __construct(string $player = "") {
        parent::__construct(self::IS_PLAYER_ONLINE, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getPlayer($source);

        yield Await::ALL;
        return $player->isOnline();
    }
}