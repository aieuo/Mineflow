<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ConfigObjectVariable;

class CreateConfigVariable extends FlowItem {

    protected $id = self::CREATE_CONFIG_VARIABLE;

    protected $name = "action.createConfigVariable.name";
    protected $detail = "action.createConfigVariable.detail";
    protected $detailDefaultReplace = ["config", "name"];

    protected $category = Category::SCRIPT;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $fileName;

    public function __construct(string $file = "", string $name = "config") {
        $this->fileName = $file;
        $this->variableName = $name;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setFileName(string $id): void {
        $this->fileName = $id;
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->fileName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getFileName()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $file = $origin->replaceVariables($this->getFileName());
        if (preg_match("#[.¥/:?<>|*\"]#u", preg_quote($file, "/@#~"))) {
            throw new InvalidFlowValueException($this->getName(), Language::get("form.recipe.invalidName"));
        }

        $variable = new ConfigObjectVariable(ConfigHolder::getConfig($file), $name);
        $origin->addVariable($variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createConfigVariable.form.name", "config", $this->getFileName(), true),
            new ExampleInput("@action.form.resultVariableName", "config", $this->getVariableName(), true),
        ];
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[0]]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setFileName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getFileName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getVariableName(), DummyVariable::CONFIG, $this->getFileName())];
    }
}