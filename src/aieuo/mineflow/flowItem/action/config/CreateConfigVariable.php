<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use SOFe\AwaitGenerator\Await;

class CreateConfigVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $fileName = "", string $variableName = "config") {
        parent::__construct(self::CREATE_CONFIG_VARIABLE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            new StringArgument("config", $variableName, "@action.form.resultVariableName", example: "config"),
            new StringArgument("name", $fileName, example: "config"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getFileName(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $file = $this->getFileName()->getString($source);
        if (!Utils::isValidFileName($file)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("form.recipe.invalidName"));
        }

        $variable = new ConfigVariable(ConfigHolder::getConfig($file));
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->getFileName()->createFormElements($variables)[0],
            $this->getVariableName()->createFormElements($variables)[0],
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ConfigVariable::class, (string)$this->getFileName())
        ];
    }
}
