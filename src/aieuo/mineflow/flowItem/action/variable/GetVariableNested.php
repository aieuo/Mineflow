<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\UnknownVariable;

class GetVariableNested extends FlowItem {
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $variableName = "",
        private string $resultName = "var",
        private string $fallbackValue = "",
    ) {
        parent::__construct(self::GET_VARIABLE_NESTED, FlowItemCategory::VARIABLE);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getResultName()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $variableName = $source->replaceVariables($this->getVariableName());
        $resultName = $source->replaceVariables($this->getResultName());

        $variable = $source->getVariable($variableName) ?? Main::getVariableHelper()->getNested($variableName);

        $fallbackValue = $this->getFallbackValue();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Main::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source);
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
            new ExampleInput("@action.getVariable.form.target", "target.hand", $this->getVariableName(), true),
            new ExampleInput("@action.form.resultVariableName", "item", $this->getResultName(), true),
            new ExampleInput("@action.getVariable.form.fallbackValue", "optional", $this->getFallbackValue(), false),
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
            $this->getResultName() => new DummyVariable(UnknownVariable::class)
        ];
    }
}
