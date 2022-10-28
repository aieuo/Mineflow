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
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class JoinListVariableToString extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(
        private string $variableName = "",
        private string $separator = "",
        private string $resultName = "result"
    ) {
        parent::__construct(self::JOIN_LIST_VARIABLE_TO_STRING, FlowItemCategory::VARIABLE);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "separator", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getSeparator(), $this->getResultName()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setSeparator(string $separator): void {
        $this->separator = $separator;
    }

    public function getSeparator(): string {
        return $this->separator;
    }

    public function setResultName(string $result): void {
        $this->resultName = $result;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->separator !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $separator = $source->replaceVariables($this->getSeparator());
        $result = $source->replaceVariables($this->getResultName());

        $variable = $source->getVariable($name) ?? $helper->getNested($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $strings = [];
        foreach ($variable->getValue() as $key => $value) {
            $strings[] = (string)$value;
        }
        $source->addVariable($result, new StringVariable(implode($separator, $strings)));

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.joinToString.form.separator", ", ", $this->getSeparator(), false),
            new ExampleInput("@action.form.resultVariableName", "string", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setSeparator($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getSeparator(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
