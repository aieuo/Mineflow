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
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\Variable;

class CreateMapVariable extends FlowItem {

    protected $id = self::CREATE_MAP_VARIABLE;

    protected $name = "action.createMapVariable.name";
    protected $detail = "action.createMapVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "key", "value"];

    protected $category = Category::VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var array */
    private $variableKey;
    /** @var array */
    private $variableValue;
    /** @var bool */
    private $isLocal;

    public function __construct(string $name = "", string $key = "", string $value = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = array_map("trim", explode(",", $key));
        $this->variableValue = array_map("trim", explode(",", $value));
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(array $variableKey) {
        $this->variableKey = $variableKey;
    }

    public function getKey(): array {
        return $this->variableKey;
    }

    public function setVariableValue(array $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): array {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and ($this->variableKey !== "");
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getKey()), implode(",", $this->getVariableValue())]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $keys = $this->getKey();
        $values = $this->getVariableValue();

        $variable = new MapVariable([], $name);
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $value = $values[$i] ?? "";
            if ($key === "") continue;

            if (!$helper->isVariableString($value)) {
                $value = $helper->replaceVariables($value, $origin->getVariables());
                $addVariable = Variable::create($helper->currentType($value), $key, $helper->getType($value));
            } else {
                $addVariable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1));
                if ($addVariable === null) {
                    $value = $helper->replaceVariables($value, $origin->getVariables());
                    $addVariable = Variable::create($helper->currentType($value), $key, $helper->getType($value));
                } else {
                    $addVariable->setName($key);
                }
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
                new ExampleInput("@action.variable.form.key", "auieo", implode(",", $this->getKey()), false),
                new ExampleInput("@action.variable.form.value", "aeiuo", implode(",", $this->getVariableValue()), false),
                new Toggle("@action.variable.form.global", !$this->isLocal),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        $name = $data[1];
        $key = array_map("trim", explode(",", $data[2]));
        $value = array_map("trim", explode(",", $data[3]));
        return ["contents" => [$name, $key, $value, !$data[4]], "cancel" => $data[5]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->getVariableValue(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getVariableName(), DummyVariable::MAP)];
    }
}