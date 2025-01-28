<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class ShowScoreboard extends SimpleAction {

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::SHOW_SCOREBOARD, FlowItemCategory::SCOREBOARD);

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

        $board->show($player);

        yield Await::ALL;
    }
}