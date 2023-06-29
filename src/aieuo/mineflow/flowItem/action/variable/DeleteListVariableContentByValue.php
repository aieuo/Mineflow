<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class DeleteListVariableContentByValue extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $variableName;
    private StringArgument $variableValue;

    public function __construct(string $variableName = "", string $variableValue = "", private bool $isLocal = true) {
        parent::__construct(self::DELETE_LIST_VARIABLE_CONTENT_BY_VALUE, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
        $this->variableValue = new StringArgument("value", $variableValue, "@action.variable.form.value", example: "auieo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->isLocal ? "local" : "global", $this->variableValue->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getValue(): StringArgument {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->variableValue->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);

        $value = $this->variableValue->get();
        if ($helper->isVariableString($value)) {
            $value = $source->getVariable(mb_substr($value, 1, -1)) ?? $helper->getNested(mb_substr($value, 1, -1));
            if ($value === null) {
                throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
            }
        } else {
            $value = new StringVariable($this->variableValue->getString($source));
        }

        $variable = $source->getVariable($name) ?? ($this->isLocal ? null : $helper->getNested($name));
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $variable->removeValue($value, false);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->variableValue->createFormElement($variables),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(2);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->variableValue->set($content[1]);
        $this->isLocal = $content[2];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->variableValue->get(), $this->isLocal];
    }
}
