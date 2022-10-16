<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScore extends FlowItem implements ScoreboardFlowItem {
    use ScoreboardFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $scoreboard = "", private string $scoreName = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);

        $this->setScoreboardVariableName($scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return ["scoreboard", "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getScoreboardVariableName(), $this->getScoreName()];
    }

    public function getScoreName(): string {
        return $this->scoreName;
    }

    public function setScoreName(string $scoreName): void {
        $this->scoreName = $scoreName;
    }

    public function isDataValid(): bool {
        return $this->getScoreboardVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getScoreName());
        $board = $this->getScoreboard($source);

        $board->removeScore($name);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
            new ExampleInput("@action.setScore.form.name", "aieuo", $this->getScoreName(), false),
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
