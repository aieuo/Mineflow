<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class DecrementScoreboardScore extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected string $id = self::DECREMENT_SCOREBOARD_SCORE;

    protected string $name = "action.decrementScore.name";
    protected string $detail = "action.decrementScore.detail";
    protected array $detailDefaultReplace = ["scoreboard", "name", "score"];

    protected string $category = Category::SCOREBOARD;

    private string $scoreName;
    private string $score;

    public function __construct(string $scoreboard = "", string $name = "", string $score = "") {
        $this->setScoreboardVariableName($scoreboard);
        $this->scoreName = $name;
        $this->score = $score;
    }

    public function getScoreName(): string {
        return $this->scoreName;
    }

    public function setScoreName(string $scoreName): void {
        $this->scoreName = $scoreName;
    }

    public function getScore(): string {
        return $this->score;
    }

    public function setScore(string $score): void {
        $this->score = $score;
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getScoreboardVariableName(), $this->getScoreName(), $this->getScore()]);
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScoreName() !== "" and $this->getScore() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getScoreName());
        $score = $source->replaceVariables($this->getScore());

        $this->throwIfInvalidNumber($score);

        $board = $this->getScoreboard($source);

        $board->setScore($name, ($board->getScore($name) ?? 0) - (int)$score);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
            new ExampleInput("@action.setScore.form.name", "aieuo", $this->getScoreName(), true),
            new ExampleNumberInput("@action.setScore.form.score", "100", $this->getScore(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setScoreboardVariableName($content[0]);
        $this->setScoreName($content[1]);
        $this->setScore($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getScoreboardVariableName(), $this->getScoreName(), $this->getScore()];
    }
}
