<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class DeleteVariable extends Action {

    protected $id = self::DELETE_VARIABLE;

    protected $name = "action.deleteVariable.name";
    protected $detail = "action.deleteVariable.detail";
    protected $detailDefaultReplace = ["name", "scope"];

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $variableName;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", bool $local = true) {
        $this->variableName = $name;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global"]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        if (!$this->isLocal) {
            Main::getVariableHelper()->delete($name);
        } else {
            $origin->removeVariable($name);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Toggle("@action.variable.form.global", !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$name, !$data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->isLocal = $content[1];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->isLocal];
    }
}