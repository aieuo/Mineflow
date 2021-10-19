<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class DeleteVariable extends FlowItem {

    protected string $id = self::DELETE_VARIABLE;

    protected string $name = "action.deleteVariable.name";
    protected string $detail = "action.deleteVariable.detail";
    protected array $detailDefaultReplace = ["name", "scope"];

    protected string $category = Category::VARIABLE;

    private string $variableName;
    private bool $isLocal;

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
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global"]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        if ($this->isLocal) {
            $source->removeVariable($name);
        } else {
            Main::getVariableHelper()->delete($name);
        }
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0], !$data[1]];
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