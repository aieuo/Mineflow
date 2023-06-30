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

    private ScoreboardArgument $scoreboard;
    private StringArgument $scoreName;

    public function __construct(string $scoreboard = "", string $scoreName = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard),
            $this->scoreName = new StringArgument("name", $scoreName, "@action.setScore.form.name", example: "aieuo", optional: true),
        ]);
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScoreName(): StringArgument {
        return $this->scoreName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->scoreName->getString($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->removeScore($name);

        yield Await::ALL;
    }
}
