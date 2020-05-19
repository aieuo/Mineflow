<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class CountListVariable extends Action {

    protected $id = self::COUNT_LIST_VARIABLE;

    protected $name = "action.countList.name";
    protected $detail = "action.countList.detail";
    protected $detailDefaultReplace = ["name", "result"];

    protected $category = Category::VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $variableName = "";
    /** @var string */
    private $resultName = "count";

    /** @var int */
    private $lastResult;

    public function __construct(string $name = "", string $result = "count") {
        $this->variableName = $name;
        $this->resultName = $result;
    }

    public function setVariableName(string $name): self {
        $this->variableName = $name;
        return $this;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getVariableName() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getResultName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $resultName = $origin->replaceVariables($this->getResultName());

        $variable = $origin->getVariable($name) ?? Main::getVariableHelper()->getNested($name);

        if (!($variable instanceof ListVariable)) {
            throw new \UnexpectedValueException(Language::get("action.countList.error.notList"));
        }

        $count = count($variable->getValue());
        $this->lastResult = $count;
        $origin->addVariable(new NumberVariable($count, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.countList.form.name", Language::get("form.example", ["list"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.countList.form.result", Language::get("form.example", ["result"]), $default[2] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return (string)$this->lastResult;
    }
}