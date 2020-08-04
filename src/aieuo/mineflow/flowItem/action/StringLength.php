<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\NumberVariable;

class StringLength extends Action {

    protected $id = self::STRING_LENGTH;

    protected $name = "action.strlen.name";
    protected $detail = "action.strlen.detail";
    protected $detailDefaultReplace = ["string", "result"];

    protected $category = Category::STRING;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $value;
    /** @var string */
    private $resultName;

    /* @var string */
    private $lastResult;

    public function __construct(string $value = "", string $resultName = "length") {
        $this->value = $value;
        $this->resultName = $resultName;
    }

    public function setValue(string $value1): self {
        $this->value = $value1;
        return $this;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getValue() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue(), $this->getResultName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $value = $origin->replaceVariables($this->getValue());
        $resultName = $origin->replaceVariables($this->getResultName());

        $length = mb_strlen($value);
        $this->lastResult = (string)$length;
        $origin->addVariable(new NumberVariable($length, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.strlen.form.value", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getValue()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["length"]), $default[2] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[2] === "") $data[2] = "length";
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setValue($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->lastResult;
    }
}