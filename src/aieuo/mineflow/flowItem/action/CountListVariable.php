<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;

class CountListVariable extends FlowItem {

    protected $id = self::COUNT_LIST_VARIABLE;

    protected $name = "action.countList.name";
    protected $detail = "action.countList.detail";
    protected $detailDefaultReplace = ["name", "result"];

    protected $category = Category::VARIABLE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $result = "count") {
        $this->variableName = $name;
        $this->resultName = $result;
    }

    public function setVariableName(string $name): self {
        $this->variableName = $name;
        return $this;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getVariableName() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $resultName = $source->replaceVariables($this->getResultName());

        $variable = $source->getVariable($name) ?? Main::getVariableHelper()->getNested($name);

        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.countList.error.notList"));
        }

        $count = count($variable->getValue());
        $source->addVariable($resultName, new NumberVariable($count));
        yield true;
        return $count;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.countList.form.name", "list", $this->getVariableName(), true),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}