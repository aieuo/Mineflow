<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;

class CreateScoreboardVariable extends FlowItem {

    protected string $id = self::CREATE_SCOREBOARD_VARIABLE;

    protected string $name = "action.createScoreboardVariable.name";
    protected string $detail = "action.createScoreboardVariable.detail";
    protected array $detailDefaultReplace = ["result", "id", "displayName", "type"];

    protected string $category = Category::SCOREBOARD;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $variableName;
    private string $boardId;
    private string $displayName;
    private string $displayType;

    private array $displayTypes = [Scoreboard::DISPLAY_SIDEBAR, Scoreboard::DISPLAY_LIST, Scoreboard::DISPLAY_BELOWNAME];

    public function __construct(string $id = "", string $displayName = "", string $type = Scoreboard::DISPLAY_SIDEBAR, string $name = "board") {
        $this->boardId = $id;
        $this->displayName = $displayName;
        $this->displayType = $type;
        $this->variableName = $name;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getBoardId(), $this->getDisplayName(), $this->getDisplayType()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $variableName = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getBoardId());
        $displayName = $source->replaceVariables($this->getDisplayName());
        $type = $this->getDisplayType();

        $scoreboard = new Scoreboard($type, $id, $displayName);

        $variable = new ScoreboardObjectVariable($scoreboard);
        $source->addVariable($variableName, $variable);
        yield FlowItemExecutor::CONTINUE;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createScoreboardVariable.form.id", "aieuo", $this->getBoardId(), true),
            new ExampleInput("@action.createScoreboardVariable.form.displayName", "auieo", $this->getDisplayName(), true),
            new Dropdown("@action.createScoreboardVariable.form.type", $this->displayTypes, array_search($this->getDisplayType(), $this->displayTypes, true)),
            new ExampleInput("@action.form.resultVariableName", "board", $this->getVariableName()),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[3], $data[0], $data[1], $this->displayTypes[$data[2]]];
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
            $this->getVariableName() => new DummyVariable(ScoreboardObjectVariable::class, $this->getDisplayName())
        ];
    }
}