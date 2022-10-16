<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use SOFe\AwaitGenerator\Await;

class ExistsVariable extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct(private string $variableName = "") {
        parent::__construct(self::EXISTS_VARIABLE, FlowItemCategory::VARIABLE);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());

        yield Await::ALL;
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
