<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;

class ExistsVariable extends Condition {

    protected $id = self::EXISTS_VARIABLE;

    protected $name = "condition.existsVariable.name";
    protected $detail = "condition.existsVariable.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Category::VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;

    public function __construct(string $name = "") {
        $this->variableName = $name;
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
        return Language::get($this->detail, [$this->getVariableName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());

        return $origin->getVariable($name) !== null or $helper->get($name) !== null or $helper->getNested($name) !== null;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $default[1] ?? $this->getVariableName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName()];
    }
}