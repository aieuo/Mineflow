<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;
use function array_unique;
use function array_values;
use function is_numeric;

class AddVariable extends SimpleAction {

    private array $variableTypes = [0 => "string", "string" => "string", 1 => "number", "number" => "number"];
    private array $variableClasses = ["string" => StringVariable::class, "number" => NumberVariable::class];

    public function __construct(string $variableName = "", string $variableValue = "", string $variableType = "string", bool $isLocal = true) {
        parent::__construct(self::ADD_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("value", $variableValue, "@action.variable.form.value")->example("aeiuo"),
            StringEnumArgument::create("type", $variableType, "@action.variable.form.type")->options(array_values(array_unique($this->variableTypes))),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getVariableValue(): StringArgument {
        return $this->getArgument("value");
    }

    public function getVariableType(): StringEnumArgument {
        return $this->getArgument("type");
    }

    public function isLocalVariable(): IsLocalVariableArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $value = $this->getVariableValue()->getString($source);
        $type = $this->getVariableType()->getEnumValue();
        $isLocal = $this->isLocalVariable()->getBool();

        switch ($type) {
            case StringVariable::getTypeName():
                $variable = new StringVariable($value);
                break;
            case NumberVariable::getTypeName():
                $value = Utils::getFloat($value);
                $variable = new NumberVariable($value);
                break;
            default:
                throw new InvalidFlowValueException($this->getName(), Language::get("action.error.recipe"));
        }

        if ($isLocal) {
            $source->addVariable($name, $variable);
        } else {
            VariableRegistry::global()->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        $class = $this->variableClasses[$this->getVariableType()->getEnumValue()];
        return [
            (string)$this->getVariableName() => new DummyVariable($class, (string)$this->getVariableValue())
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getVariableName(),
                $this->getVariableValue(),
                $this->getVariableType(),
                $this->isLocalVariable(),
            ], formResponseValidator: function (array $data) {
                $containsVariable = Mineflow::getVariableHelper()->containsVariable($data[1]);

                if ($data[2] === NumberVariable::getTypeName() and !$containsVariable and !is_numeric($data[1])) {
                    throw new InvalidFormValueException(Language::get("action.error.notNumber", [$data[3]]), 1);
                }
            }),
        ];
    }
}