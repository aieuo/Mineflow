<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;

class GetVariableNested extends FlowItem {

    protected $id = self::GET_VARIABLE_NESTED;

    protected $name = "action.getVariableNested.name";
    protected $detail = "action.getVariableNested.detail";
    protected $detailDefaultReplace = ["name", "result"];

    protected $category = Category::VARIABLE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $result = "var") {
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

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $variableName = $source->replaceVariables($this->getVariableName());
        $resultName = $source->replaceVariables($this->getResultName());

        $variable = $source->getVariable($variableName);
        if (!($variable instanceof Variable)) {
            $variable = Main::getVariableHelper()->getNested($variableName);
            if (!($variable instanceof Variable)) {
                throw new InvalidFlowValueException($this->getName(), "§cUndefined variable: ".$variableName);
            }
        }

        $variable->setName($resultName);
        $source->addVariable($variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getVariableNested.form.target", "target.hand", $this->getVariableName(), true),
            new ExampleInput("@action.form.resultVariableName", "item", $this->getResultName(), true),
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
        return [new DummyVariable($this->getResultName(), DummyVariable::UNKNOWN)];
    }
}