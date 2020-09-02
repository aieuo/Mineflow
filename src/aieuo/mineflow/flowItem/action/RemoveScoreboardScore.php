<?php

namespace aieuo\mineflow\flowItem\action;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class RemoveScoreboardScore extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected $id = self::REMOVE_SCOREBOARD_SCORE;

    protected $name = "action.removeScore.name";
    protected $detail = "action.removeScore.detail";
    protected $detailDefaultReplace = ["scoreboard", "name"];

    protected $category = Category::SCOREBOARD;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /* @var string */
    private $scoreName;

    public function __construct(string $scoreboard = "board", string $name = "") {
        $this->setScoreboardVariableName($scoreboard);
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getScoreName());

        $board = $this->getScoreboard($origin);
        $this->throwIfInvalidScoreboard($board);

        $board->removeScore($name);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.scoreboard", "board", $default[1] ?? $this->getScoreboardVariableName(), true),
                new ExampleInput("@action.setScore.form.name", "aieuo", $default[2] ?? $this->getScoreName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setScoreboardVariableName($content[0]);
        $this->setScoreName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getScoreboardVariableName(), $this->getScoreName()];
    }
}
