<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use SOFe\AwaitGenerator\Await;

class CreateConfigVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private StringArgument $fileName;
    private StringArgument $variableName;

    public function __construct(string $fileName = "", string $variableName = "config") {
        parent::__construct(self::CREATE_CONFIG_VARIABLE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            $this->variableName = new StringArgument("config", $variableName, "@action.form.resultVariableName", example: "config"),
            $this->fileName = new StringArgument("name", $fileName, example: "config"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getFileName(): StringArgument {
        return $this->fileName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $file = $this->fileName->getString($source);
        if (!Utils::isValidFileName($file)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("form.recipe.invalidName"));
        }

        $variable = new ConfigVariable(ConfigHolder::getConfig($file));
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->variableName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->fileName->createFormElement($variables),
            $this->variableName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(ConfigVariable::class, $this->fileName->get())
        ];
    }
}
