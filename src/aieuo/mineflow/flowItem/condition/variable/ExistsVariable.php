<?php

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;

class ExistsVariable extends FlowItem implements Condition {

    protected string $name = "condition.existsVariable.name";
    protected string $detail = "condition.existsVariable.detail";
    protected array $detailDefaultReplace = ["name"];

    private string $variableName;

    public function __construct(string $name = "") {
        parent::__construct(self::EXISTS_VARIABLE, FlowItemCategory::VARIABLE);

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());

        yield true;
        return $source->getVariable($name) !== null or $helper->get($name) !== null or $helper->getNested($name) !== null;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName()];
    }
}