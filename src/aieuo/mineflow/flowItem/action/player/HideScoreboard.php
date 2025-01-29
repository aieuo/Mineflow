<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class HideScoreboard extends SimpleAction {

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::HIDE_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            ScoreboardArgument::create("scoreboard", $scoreboard),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->getArgument("scoreboard");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $board = $this->getScoreboard()->getScoreboard($source);

        $board->hide($player);

        yield Await::ALL;
    }
}