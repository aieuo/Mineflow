<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class DeleteVariable extends FlowItem {

    protected $id = self::DELETE_VARIABLE;

    protected $name = "action.deleteVariable.name";
    protected $detail = "action.deleteVariable.detail";
    protected $detailDefaultReplace = ["name", "scope"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $variableName;
    /** @var bool */
    private $isLocal;

    public function __construct(string $name = "", bool $local = true) {
        $this->variableName = $name;
        $this->isLocal = $local;
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
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global"]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        if ($this->isLocal) {
            $origin->removeVariable($name);
        } else {
            Main::getVariableHelper()->delete($name);
        }
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
                new Toggle("@action.variable.form.global", !$this->isLocal),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], !$data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->isLocal = $content[1];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->isLocal];
    }
}