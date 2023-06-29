<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use SOFe\AwaitGenerator\Await;

class GetVariableNested extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private StringArgument $variableName;
    private StringArgument $resultName;
    private StringArgument $fallbackValue;

    public function __construct(string $variableName = "", string $resultName = "var",string $fallbackValue = "") {
        parent::__construct(self::GET_VARIABLE_NESTED, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, example: "target.hand");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "item");
        $this->fallbackValue = new StringArgument("fallbackValue", $fallbackValue, example: "optional", optional: true);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->resultName->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function getFallbackValue(): StringArgument {
        return $this->fallbackValue;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and !empty($this->resultName->get());
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->variableName->getString($source);
        $resultName = $this->resultName->getString($source);

        $variable = $source->getVariable($variableName) ?? Mineflow::getVariableHelper()->getNested($variableName);

        $fallbackValue = $this->fallbackValue->get();
        if ($fallbackValue !== "" and $variable === null) {
            $variable = Mineflow::getVariableHelper()->copyOrCreateVariable($fallbackValue, $source);
        }

        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$variableName]));
        }

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->resultName->createFormElement($variables),
            $this->fallbackValue->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->resultName->set($content[1]);
        $this->fallbackValue->set($content[2] ?? "");
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->resultName->get(), $this->fallbackValue->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(UnknownVariable::class)
        ];
    }
}
