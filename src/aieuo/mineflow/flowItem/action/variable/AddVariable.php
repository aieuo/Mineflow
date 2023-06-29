<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;
use function array_search;
use function array_unique;
use function array_values;
use function is_int;
use function is_numeric;

class AddVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private string $variableType;


    private array $variableTypes = [0 => "string", "string" => "string", 1 => "number", "number" => "number"];
    private array $variableClasses = ["string" => StringVariable::class, "number" => NumberVariable::class];

    private StringArgument $variableName;
    private StringArgument $variableValue;

    public function __construct(
        string       $variableName = "",
        string       $variableValue = "",
        string       $type = null,
        private bool $isLocal = true
    ) {
        parent::__construct(self::ADD_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableType = $type ?? StringVariable::getTypeName();

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
        $this->variableValue = new StringArgument("value", $variableValue, "@action.variable.form.value", example: "aeiuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "value", "type", "scope"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->variableValue->get(), $this->variableTypes[$this->variableType], $this->isLocal ? "local" : "global"];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getVariableValue(): StringArgument {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName->isValid() and $this->variableValue->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $value = $this->variableValue->getString($source);

        switch ($this->variableType) {
            case StringVariable::getTypeName():
                $variable = new StringVariable($value);
                break;
            case NumberVariable::getTypeName():
                $value = $this->getFloat($value);
                $variable = new NumberVariable($value);
                break;
            default:
                throw new InvalidFlowValueException($this->getName(), Language::get("action.error.recipe"));
        }

        if ($this->isLocal) {
            $source->addVariable($name, $variable);
        } else {
            Mineflow::getVariableHelper()->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $index = array_search($this->variableType, $this->variableTypes, true);
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->variableValue->createFormElement($variables),
            new Dropdown("@action.variable.form.type", array_values(array_unique($this->variableTypes)), $index === false ? 0 : $index),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocess(function (array $data) {
                $containsVariable = Mineflow::getVariableHelper()->containsVariable($data[1]);
                if ($data[2] === NumberVariable::getTypeName() and !$containsVariable and !is_numeric($data[1])) {
                    throw new InvalidFormValueException(Language::get("action.error.notNumber", [$data[3]]), 1);
                }

                return [$data[0], $data[1], $data[2], !$data[3]];
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->variableValue->set($content[1]);
        $this->variableType = is_int($content[2]) ? $this->variableTypes[$content[2]] : $content[2];
        $this->isLocal = $content[3];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->variableValue->get(), $this->variableType, $this->isLocal];
    }

    public function getAddingVariables(): array {
        $class = $this->variableClasses[$this->variableType];
        return [
            $this->variableName->get() => new DummyVariable($class, $this->variableValue->get())
        ];
    }
}
