<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use SOFe\AwaitGenerator\Await;

class SetSleeping extends SimpleAction {

    private PlayerPlaceholder $player;
    private PositionPlaceholder $position;

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SLEEPING, FlowItemCategory::PLAYER);

        $this->setPlaceholders([
            $this->player = new PlayerPlaceholder("player", $player),
            $this->position = new PositionPlaceholder("position", $position),
        ]);
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $position = $this->position->getPosition($source);

        $player->sleepOn($position);

        yield Await::ALL;
    }
}
