<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class RemoveScoreboardScore extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;

    protected string $id = self::REMOVE_SCOREBOARD_SCORE;

    protected string $name = "action.removeScore.name";
    protected string $detail = "action.removeScore.detail";
    protected array $detailDefaultReplace = ["scoreboard", "name"];

    protected string $category = Category::SCOREBOARD;

    private string $scoreName;

    public function __construct(string $scoreboard = "", string $name = "") {
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
        return Language::get($this->detail, [$this->getScoreboardVariableName(), $this->getScoreName()]);
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "" and $this->getScoreName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getScoreName());

        $board = $this->getScoreboard($source);

        $board->removeScore($name);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
            new ExampleInput("@action.setScore.form.name", "aieuo", $this->getScoreName(), true),
        ];
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
