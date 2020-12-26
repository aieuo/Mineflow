<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ExistsVariable extends FlowItem implements Condition {

    protected $id = self::EXISTS_VARIABLE;

    protected $name = "condition.existsVariable.name";
    protected $detail = "condition.existsVariable.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $variableName;

    public function __construct(string $name = "") {
        $this->variableName = $name;
    }

    public function setVariableName(string $variableName): void {
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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());

        yield true;
        return $origin->getVariable($name) !== null or $helper->get($name) !== null or $helper->getNested($name) !== null;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName()];
    }
}