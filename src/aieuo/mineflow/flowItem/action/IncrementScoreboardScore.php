<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class IncrementScoreboardScore extends Action implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected $id = self::INCREMENT_SCOREBOARD_SCORE;

    protected $name = "action.incrementScore.name";
    protected $detail = "action.incrementScore.detail";
    protected $detailDefaultReplace = ["scoreboard", "name", "score"];

    protected $category = Category::SCOREBOARD;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /* @var string */
    private $scoreName;
    /* @var string */
    private $score;

    public function __construct(string $position = "board", string $name = "", string $score = "") {
        $this->scoreboardVariableName = $position;
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
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getScoreboardVariableName(), $this->getScoreName(), $this->getScore()]);
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScoreName() !== "" and $this->getScore() !== "";
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getScoreName());
        $score = $origin->replaceVariables($this->getScore());

        $this->throwIfInvalidNumber($score);

        $board = $this->getScoreboard($origin);
        $this->throwIfInvalidScoreboard($board);

        $board->setScore($name, ($board->getScore($name) ?? 0) + (int)$score);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.scoreboard", Language::get("form.example", ["board"]), $default[1] ?? $this->getScoreboardVariableName()),
                new Input("@action.setScore.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getScoreName()),
                new Input("@action.setScore.form.score", Language::get("form.example", ["100"]), $default[3] ?? $this->getScore()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "board";
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $errors[] = ["@form.insufficient", 3];
        if (!is_numeric($data[3]) and !Main::getVariableHelper()->containsVariable($data[3])) {
            $errors[] = ["@flowItem.error.notNumber", 3];
        }
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setScoreboardVariableName($content[0]);
        $this->setScoreName($content[1]);
        $this->setScore($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getScoreboardVariableName(), $this->getScoreName(), $this->getScore()];
    }
}
