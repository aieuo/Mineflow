<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;

class RemoveScoreboardScoreName extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected string $name = "action.removeScoreName.name";
    protected string $detail = "action.removeScoreName.detail";
    protected array $detailDefaultReplace = ["scoreboard", "score"];

    public function __construct(string $scoreboard = "", private string $score = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->setScoreboardVariableName($scoreboard);
    }

    public function getScore(): string {
        return $this->score;
    }

    public function setScore(string $score): void {
        $this->score = $score;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getScoreboardVariableName(), $this->getScore()]);
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScore() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $score = $source->replaceVariables($this->getScore());

        $this->throwIfInvalidNumber($score);

        $board = $this->getScoreboard($source);

        $board->removeScoreName((int)$score);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
            new ExampleNumberInput("@action.setScore.form.score", "100", $this->getScore(), true),
        ];
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
