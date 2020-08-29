<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

abstract class TypeGetMathVariable extends Action {

    protected $detailDefaultReplace = ["result"];

    protected $category = Category::MATH;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    protected $resultName = "result";
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var float|int */
    protected $lastResult;

    public function __construct(?string $result = "") {
        $this->resultName = empty($result) ? $this->resultName : $result;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getResultName()]);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.resultVariableName", "result", $default[1] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (isset($content[0])) $this->setResultName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getResultName()];
    }

    public function getReturnValue(): string {
        return (string)$this->lastResult;
    }
}