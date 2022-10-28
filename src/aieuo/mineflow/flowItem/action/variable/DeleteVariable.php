<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class DeleteVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(
        private string $variableName = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::DELETE_VARIABLE, FlowItemCategory::VARIABLE);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->isLocal ? "local" : "global"];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
        if ($this->isLocal) {
            $source->removeVariable($name);
        } else {
            Mineflow::getVariableHelper()->delete($name);
        }

        yield Await::ALL;
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
