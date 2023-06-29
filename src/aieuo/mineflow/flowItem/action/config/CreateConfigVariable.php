<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use SOFe\AwaitGenerator\Await;

class CreateConfigVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    
    private StringArgument $fileName;
    private StringArgument $variableName;

    public function __construct(string $fileName = "", string $variableName = "config") {
        parent::__construct(self::CREATE_CONFIG_VARIABLE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->variableName = new StringArgument("config", $variableName, "@action.form.resultVariableName", example: "config");
        $this->fileName = new StringArgument("name", $fileName, example: "config");
    }

    public function getDetailDefaultReplaces(): array {
        return ["config", "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->fileName->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getFileName(): StringArgument {
        return $this->fileName;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->fileName->isNotEmpty();
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
            $this->fileName->isNotEmpty(),
            $this->variableName->isNotEmpty(),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->fileName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->fileName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(ConfigVariable::class, $this->fileName->get())
        ];
    }
}
