<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class CreateConfigVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $fileName = "", string $variableName = "config") {
        parent::__construct(self::CREATE_CONFIG_VARIABLE, FlowItemCategory::CONFIG, [FlowItemPermission::CONFIG]);

        $this->setArguments([
            StringArgument::create("config", $variableName, "@action.form.resultVariableName")->example("config"),
            StringArgument::create("name", $fileName)->example("config"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("config");
    }

    public function getFileName(): StringArgument {
        return $this->getArgument("name");
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

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ConfigVariable::class, (string)$this->getFileName())
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getFileName(),
                $this->getVariableName(),
            ]),
        ];
    }
}