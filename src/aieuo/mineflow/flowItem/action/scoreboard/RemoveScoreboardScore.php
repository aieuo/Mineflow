<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScore extends SimpleAction {

    public function __construct(string $scoreboard = "", string $scoreName = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            ScoreboardArgument::create("scoreboard", $scoreboard),
            StringArgument::create("name", $scoreName, "@action.setScore.form.name")->optional()->example("aieuo"),
        ]);
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->getArgument("scoreboard");
    }

    public function getScoreName(): StringArgument {
        return $this->getArgument("name");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getScoreName()->getString($source);
        $board = $this->getScoreboard()->getScoreboard($source);

        $board->removeScore($name);

        yield Await::ALL;
    }
}