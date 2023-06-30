<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class HideScoreboard extends SimpleAction {

    private PlayerArgument $player;
    private ScoreboardArgument $scoreboard;

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::HIDE_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->hide($player);

        yield Await::ALL;
    }
}
