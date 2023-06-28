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

    private ScoreboardArgument $scoreboard;
    private NumberArgument $score;

    public function __construct(string $scoreboard = "", int $score = null) {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard),
            $this->score = new NumberArgument("score", $score, "@action.setScore.form.score", example: "100"),
        ]);
    }
    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScore(): NumberArgument {
        return $this->score;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $score = $this->score->getInt($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->removeScoreName($score);

        yield Await::ALL;
    }
}
