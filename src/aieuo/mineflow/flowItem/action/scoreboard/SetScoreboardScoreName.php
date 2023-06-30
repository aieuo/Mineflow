<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetScoreboardScoreName extends SimpleAction {

    private ScoreboardArgument $scoreboard;
    private StringArgument $scoreName;
    private NumberArgument $score;

    public function __construct(string $scoreboard = "", string $scoreName = "", string $score = "") {
        parent::__construct(self::SET_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard),
            $this->scoreName = new StringArgument("name", $scoreName, "@action.setScore.form.name", example: "aieuo", optional: true),
            $this->score = new NumberArgument("score", $score, "@action.setScore.form.score", example: "100"),
        ]);
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScoreName(): StringArgument {
        return $this->scoreName;
    }

    public function getScore(): NumberArgument {
        return $this->score;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->scoreName->getString($source);
        $score = $this->score->getInt($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->setScoreName($name, $score);

        yield Await::ALL;
    }
}
