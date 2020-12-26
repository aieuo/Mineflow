<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;

class JoinListVariableToString extends FlowItem {

    protected $id = self::JOIN_LIST_VARIABLE_TO_STRING;

    protected $name = "action.joinToString.name";
    protected $detail = "action.joinToString.detail";
    protected $detailDefaultReplace = ["name", "separator", "result"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $separator;
    /** @var string */
    private $variableName;
    /* @var string */
    private $resultName;

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $separator = $origin->replaceVariables($this->getSeparator());
        $result = $origin->replaceVariables($this->getResultName());

        $variable = $origin->getVariables()[$name] ?? $helper->get($name) ?? new ListVariable([], $name);
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error", [
                $this->getName(), ["action.addListVariable.error.existsOtherType", [$name, (string)$variable]]
            ]));
        }

        $strings = [];
        foreach ($variable->getValue() as $key => $value) {
            $strings[] = (string)$value;
        }
        $origin->addVariable(new StringVariable(implode($separator, $strings), $result));
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
                new ExampleInput("@action.joinToString.form.separator", ", ", $this->getSeparator(), false),
                new ExampleInput("@action.form.resultVariableName", "string", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
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
        return [new DummyVariable($this->getResultName(), DummyVariable::STRING)];
    }
}