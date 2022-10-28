<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;

class JoinListVariableToString extends FlowItem {

    protected string $id = self::JOIN_LIST_VARIABLE_TO_STRING;

    protected string $name = "action.joinToString.name";
    protected string $detail = "action.joinToString.detail";
    protected array $detailDefaultReplace = ["name", "separator", "result"];

    protected string $category = FlowItemCategory::VARIABLE;

    private string $separator;
    private string $variableName;
    private string $resultName;

    public function __construct(string $name = "", string $separator = "", string $result = "result") {
        $this->variableName = $name;
        $this->separator = $separator;
        $this->resultName = $result;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getSeparator(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Mineflow::getVariableHelper();
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
        yield true;
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
            $this->getResultName() => new DummyVariable(DummyVariable::STRING)
        ];
    }
}
