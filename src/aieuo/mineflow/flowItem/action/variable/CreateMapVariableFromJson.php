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
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;
use function array_is_list;

class CreateMapVariableFromJson extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $variableName;
    private StringArgument $json;

    public function __construct(string $variableName = "", string $json = "", private bool $isLocal = true) {
        parent::__construct(self::CREATE_MAP_VARIABLE_FROM_JSON, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
        $this->json = new StringArgument("json", $json, "@action.variable.form.value", example: "aeiuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope", "json"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->isLocal ? "local" : "global", $this->json->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getJson(): StringArgument {
        return $this->json;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->json->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $json = $this->json->get();

        $value = json_decode($json, true);
        if ($value === null) {
            throw new InvalidFlowValueException($this->getName(), json_last_error_msg());
        }

        if (array_is_list($value)) {
            $variable = new ListVariable(Mineflow::getVariableHelper()->toVariableArray($value));
        } else {
            $variable = new MapVariable(Mineflow::getVariableHelper()->toVariableArray($value));
        }

        if ($this->isLocal) {
            $source->addVariable($name, $variable);
        } else {
            $helper->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->json->createFormElement($variables),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(2);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->json->set($content[1]);
        $this->isLocal = $content[2];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->json->get(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
