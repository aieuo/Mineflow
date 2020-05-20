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

class RemoveScoreboardScore extends Action implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected $id = self::REMOVE_SCOREBOARD_SCORE;

    protected $name = "action.removeScore.name";
    protected $detail = "action.removeScore.detail";
    protected $detailDefaultReplace = ["scoreboard", "name"];

    protected $category = Category::SCOREBOARD;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /* @var string */
    private $scoreName;

    public function __construct(string $position = "board", string $name = "") {
        $this->scoreboardVariableName = $position;
        $this->scoreName = $name;
    }

    public function getScoreName(): string {
        return $this->scoreName;
    }

    public function setScoreName(string $scoreName): void {
        $this->scoreName = $scoreName;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getScoreboardVariableName(), $this->getScoreName()]);
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScoreName() !== "";
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getScoreName());

        $board = $this->getScoreboard($origin);
        $this->throwIfInvalidScoreboard($board);

        $board->removeScore($name);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.scoreboard", Language::get("form.example", ["board"]), $default[1] ?? $this->getScoreboardVariableName()),
                new Input("@action.setScore.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getScoreName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "board";
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        return ["status" => true, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setScoreboardVariableName($content[0]);
        $this->setScoreName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getScoreboardVariableName(), $this->getScoreName()];
    }
}
