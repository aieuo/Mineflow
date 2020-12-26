<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;

class CreateScoreboardVariable extends FlowItem {

    protected $id = self::CREATE_SCOREBOARD_VARIABLE;

    protected $name = "action.createScoreboardVariable.name";
    protected $detail = "action.createScoreboardVariable.detail";
    protected $detailDefaultReplace = ["result", "id", "displayName", "type"];

    protected $category = Category::SCOREBOARD;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $boardId;
    /** @var string */
    private $displayName;
    /* @var string */
    private $displayType;

    private $displayTypes = [Scoreboard::DISPLAY_SIDEBAR, Scoreboard::DISPLAY_LIST, Scoreboard::DISPLAY_BELOWNAME];

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $variableName = $origin->replaceVariables($this->getVariableName());
        $id = $origin->replaceVariables($this->getBoardId());
        $displayName = $origin->replaceVariables($this->getDisplayName());
        $type = $this->getDisplayType();

        $scoreboard = new Scoreboard($type, $id, $displayName);

        $variable = new ScoreboardObjectVariable($scoreboard, $variableName);
        $origin->addVariable($variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.createScoreboardVariable.form.id", "aieuo", $this->getBoardId(), true),
                new ExampleInput("@action.createScoreboardVariable.form.displayName", "auieo", $this->getDisplayName(), true),
                new Dropdown("@action.createScoreboardVariable.form.type", $this->displayTypes, array_search($this->getDisplayType(), $this->displayTypes, true)),
                new ExampleInput("@action.form.resultVariableName", "board", $this->getVariableName()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[4], $data[1], $data[2], $this->displayTypes[$data[3]]], "cancel" => $data[5]];
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
        return [new DummyVariable($this->getVariableName(), DummyVariable::SCOREBOARD, $this->getDisplayName())];
    }
}