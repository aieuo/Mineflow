<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class IncrementScoreboardScore extends SimpleAction {

    public function __construct(string $scoreboard = "", string $scoreName = "", int $score = null) {
        parent::__construct(self::INCREMENT_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);


        $this->setArguments([
            ScoreboardArgument::create("scoreboard", $scoreboard),
            StringArgument::create("name", $scoreName, "@action.setScore.form.name")->optional()->example("aieuo"),
            NumberArgument::create("score", $score, "@action.setScore.form.score")->example("100"),
        ]);
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->getArgument("scoreboard");
    }

    public function getScoreName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getScore(): NumberArgument {
        return $this->getArgument("score");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getScoreName()->getString($source);
        $score = $this->getScore()->getInt($source);
        $board = $this->getScoreboard()->getScoreboard($source);

        $board->setScore($name, ($board->getScore($name) ?? 0) + $score);

        yield Await::ALL;
    }
}