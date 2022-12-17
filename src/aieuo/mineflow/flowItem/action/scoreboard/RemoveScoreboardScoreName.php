<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScoreName extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $scoreboard = "", private string $score = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->setScoreboardVariableName($scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return ["scoreboard", "score"];
    }

    public function getDetailReplaces(): array {
        return [$this->getScoreboardVariableName(), $this->getScore()];
    }

    public function getScore(): string {
        return $this->score;
    }

    public function setScore(string $score): void {
        $this->score = $score;
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScore() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $score = $this->getInt($source->replaceVariables($this->getScore()));
        $board = $this->getScoreboard($source);

        $board->removeScoreName($score);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
            new ExampleNumberInput("@action.setScore.form.score", "100", $this->getScore(), true),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setScoreboardVariableName($content[0]);
        $this->setScore($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getScoreboardVariableName(), $this->getScore()];
    }
}
