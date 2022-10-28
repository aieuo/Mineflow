<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;

class GetVariableNested extends FlowItem {

    protected string $id = self::GET_VARIABLE_NESTED;

    protected string $name = "action.getVariableNested.name";
    protected string $detail = "action.getVariableNested.detail";
    protected array $detailDefaultReplace = ["name", "result"];

    protected string $category = FlowItemCategory::VARIABLE;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $variableName = "",
        private string $resultName = "var",
        private string $fallbackValue = "",
    ) {
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

    public function setFallbackValue(string $fallbackValue): void {
        $this->fallbackValue = $fallbackValue;
    }

    public function getFallbackValue(): string {
        return $this->fallbackValue;
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

        $variableName = $source->replaceVariables($this->getVariableName());
        $resultName = $source->replaceVariables($this->getResultName());

        $variable = $source->getVariable($variableName) ?? Mineflow::getVariableHelper()->getNested($variableName);

        $fallbackValue = $this->getFallbackValue();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Mineflow::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source);
        }

        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$variableName]));
        }

        $source->addVariable($resultName, $variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getVariableNested.form.target", "target.hand", $this->getVariableName(), true),
            new ExampleInput("@action.form.resultVariableName", "item", $this->getResultName(), true),
            new ExampleInput("@action.getVariableNested.form.fallbackValue", "optional", $this->getFallbackValue(), false),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setResultName($content[1]);
        $this->setFallbackValue($content[2] ?? "");
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getResultName(), $this->getFallbackValue()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(DummyVariable::UNKNOWN)
        ];
    }
}
