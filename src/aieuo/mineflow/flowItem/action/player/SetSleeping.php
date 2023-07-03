<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetSleeping extends SimpleAction {

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SLEEPING, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new PositionArgument("position", $position),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $position = $this->getPosition()->getPosition($source);

        $player->sleepOn($position);

        yield Await::ALL;
    }
}
