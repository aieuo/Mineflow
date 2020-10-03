<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\Variable;

class CreateListVariable extends FlowItem {

    protected $id = self::CREATE_LIST_VARIABLE;

    protected $name = "action.createListVariable.name";
    protected $detail = "action.createListVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "value"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $variableName;
    /** @var string[] */
    private $variableValue;
    /** @var bool */
    private $isLocal;

    public function __construct(string $value = "", string $name = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = array_map("trim", explode(",", $value));
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setVariableValue(array $variableValue): void {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): array {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getVariableValue())]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $values = $this->getVariableValue();

        $variable = new ListVariable([], $name);

        foreach ($values as $value) {
            if ($value === "") continue;
            if ($helper->isVariableString($value)) {
                $addVariable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1));
                if ($addVariable === null) {
                    $value = $helper->replaceVariables($value, $origin->getVariables());
                    $addVariable = Variable::create($helper->currentType($value), "", $helper->getType($value));
                }
            } else {
                $value = $helper->replaceVariables($value, $origin->getVariables());
                $addVariable = Variable::create($helper->currentType($value), "", $helper->getType($value));
            }

            $variable->addValue($addVariable);
        }

        if ($this->isLocal) {
            $origin->addVariable($variable);
        } else {
            $helper->add($variable);
        }
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
                new ExampleInput("@action.variable.form.value", "aiueo", implode(",", $this->getVariableValue()), true),
                new Toggle("@action.variable.form.global", !$this->isLocal),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], array_map("trim", explode(",", $data[2])), !$data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getVariableName(), DummyVariable::LIST)];
    }
}