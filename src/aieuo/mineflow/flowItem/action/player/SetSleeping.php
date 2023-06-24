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

    private PlayerArgument $player;
    private PositionArgument $position;

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SLEEPING, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->position = new PositionArgument("position", $position),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $position = $this->position->getPosition($source);

        $player->sleepOn($position);

        yield Await::ALL;
    }
}
