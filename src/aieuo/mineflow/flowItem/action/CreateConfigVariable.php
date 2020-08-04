<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigObjectVariable;

class CreateConfigVariable extends Action {

    protected $id = self::CREATE_CONFIG_VARIABLE;

    protected $name = "action.createConfigVariable.name";
    protected $detail = "action.createConfigVariable.detail";
    protected $detailDefaultReplace = ["config", "name"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $fileName;

    public function __construct(string $file = "", string $name = "config") {
        $this->fileName = $file;
        $this->variableName = $name;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setFileName(string $id) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $file = $origin->replaceVariables($this->getFileName());
        if (preg_match("#[.¥/:?<>|*\"]#", preg_quote($file))) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["form.recipe.invalidName"]]));
        }

        $variable = new ConfigObjectVariable(ConfigHolder::getConfig($file), $name);
        $origin->addVariable($variable);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.createConfigVariable.form.name", Language::get("form.example", ["config"]), $default[1] ?? $this->getFileName()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["config"]), $default[2] ?? $this->getVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif (preg_match("#[.¥/:?<>|*\"]#", preg_quote($data[1]))) {
            $errors[] = ["@form.recipe.invalidName", 1];
        }
        if ($data[2] === "") $data[2] = "config";
        return ["contents" => [$data[2], $data[1]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setFileName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getFileName()];
    }

    public function getReturnValue(): string {
        return $this->getVariableName();
    }
}