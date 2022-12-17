<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use SOFe\AwaitGenerator\Await;

class CreateScoreboardVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private array $displayTypes = [Scoreboard::DISPLAY_SIDEBAR, Scoreboard::DISPLAY_LIST, Scoreboard::DISPLAY_BELOWNAME];

    public function __construct(
        private string $boardId = "",
        private string $displayName = "",
        private string $displayType = Scoreboard::DISPLAY_SIDEBAR,
        private string $variableName = "board"
    ) {
        parent::__construct(self::CREATE_SCOREBOARD_VARIABLE, FlowItemCategory::SCOREBOARD);
    }

    public function getDetailDefaultReplaces(): array {
        return ["result", "id", "displayName", "type"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getBoardId(), $this->getDisplayName(), $this->getDisplayType()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setBoardId(string $boardId): void {
        $this->boardId = $boardId;
    }

    public function getBoardId(): string {
        return $this->boardId;
    }

    public function setDisplayName(string $displayName): void {
        $this->displayName = $displayName;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function setDisplayType(string $displayType): void {
        $this->displayType = $displayType;
    }

    public function getDisplayType(): string {
        return $this->displayType;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->boardId !== "" and $this->displayName !== "" and in_array($this->displayType, $this->displayTypes, true);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getBoardId());
        $displayName = $source->replaceVariables($this->getDisplayName());
        $type = $this->getDisplayType();

        $scoreboard = new Scoreboard($type, $id, $displayName);

        $variable = new ScoreboardVariable($scoreboard);
        $source->addVariable($variableName, $variable);

        yield Await::ALL;
        return $this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.createScoreboard.form.id", "aieuo", $this->getBoardId(), true),
            new ExampleInput("@action.createScoreboard.form.displayName", "auieo", $this->getDisplayName(), true),
            new Dropdown("@action.createScoreboard.form.type", $this->displayTypes, array_search($this->getDisplayType(), $this->displayTypes, true)),
            new ExampleInput("@action.form.resultVariableName", "board", $this->getVariableName()),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(2, fn($value) => $this->displayTypes[$value]);
            $response->rearrange([3, 0, 1, 2]);
        });
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setBoardId($content[1]);
        $this->setDisplayName($content[2]);
        $this->setDisplayType($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getBoardId(), $this->getDisplayName(), $this->getDisplayType()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName() => new DummyVariable(ScoreboardVariable::class, $this->getDisplayName())
        ];
    }
}
