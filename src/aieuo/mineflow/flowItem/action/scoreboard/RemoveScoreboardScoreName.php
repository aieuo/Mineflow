<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScoreName extends SimpleAction {

    public function __construct(string $scoreboard = "", int $score = null) {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            ScoreboardArgument::create("scoreboard", $scoreboard),
            NumberArgument::create("score", $score, "@action.setScore.form.score")->example("100"),
        ]);
    }
    public function getScoreboard(): ScoreboardArgument {
        return $this->getArgument("scoreboard");
    }

    public function getScore(): NumberArgument {
        return $this->getArgument("score");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $score = $this->getScore()->getInt($source);
        $board = $this->getScoreboard()->getScoreboard($source);

        $board->removeScoreName($score);

        yield Await::ALL;
    }
}
