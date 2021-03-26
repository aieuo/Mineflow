<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;

class AddVariable extends FlowItem {

    protected $id = self::ADD_VARIABLE;

    protected $name = "action.addVariable.name";
    protected $detail = "action.addVariable.detail";
    protected $detailDefaultReplace = ["name", "value", "type", "scope"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableValue;
    /** @var int */
    private $variableType;
    /** @var bool */
    private $isLocal;

    /** @var array */
    private $variableTypes = ["string", "number"];

    public function __construct(string $name = "", string $value = "", int $type = Variable::STRING, bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = $value;
        $this->variableType = $type;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setVariableValue(string $variableValue): void {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->variableValue !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getVariableValue(), $this->variableTypes[$this->variableType], $this->isLocal ? "local" : "global"]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $value = $source->replaceVariables($this->getVariableValue());

        switch ($this->variableType) {
            case Variable::STRING:
                $variable = new StringVariable($value);
                break;
            case Variable::NUMBER:
                $this->throwIfInvalidNumber($value);
                $variable = new NumberVariable((float)$value);
                break;
            default:
                throw new InvalidFlowValueException($this->getName(), Language::get("action.error.recipe"));
        }

        if ($this->isLocal) {
            $source->addVariable($name, $variable);
        } else {
            Main::getVariableHelper()->add($name, $variable);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.value", "aeiuo", $this->getVariableValue(), true),
            new Dropdown("@action.variable.form.type", $this->variableTypes, $this->variableType),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        $containsVariable = Main::getVariableHelper()->containsVariable($data[1]);
        if ($data[2] === Variable::NUMBER and !$containsVariable and !is_numeric($data[1])) {
            throw new InvalidFormValueException(Language::get("action.error.notNumber", [$data[3]]), 1);
        }

        return [$data[0], $data[1], $data[2], !$data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->variableType = $content[2];
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->variableType, $this->isLocal];
    }

    public function getAddingVariables(): array {
        $type = $this->variableTypes[$this->variableType];
        return [
            $this->getVariableName() => new DummyVariable($type, $this->getVariableValue())
        ];
    }
}