<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GetDistance extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        string         $pos1 = "",
        string         $pos2 = "",
        private string $resultName = "distance"
    ) {
        parent::__construct(self::GET_DISTANCE, FlowItemCategory::WORLD);

        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
    }

    public function getDetailDefaultReplaces(): array {
        return ["pos1", "pos2", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()];
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName("pos1") !== "" and $this->getPositionVariableName("pos2") !== "" and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");
        $result = $source->replaceVariables($this->getResultName());

        $distance = $pos1->distance($pos2);

        $source->addVariable($result, new NumberVariable($distance));

        yield Await::ALL;
        return $distance;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getDistance.form.pos1", "pos1", $this->getPositionVariableName("pos1"), true),
            new ExampleInput("@action.getDistance.form.pos2", "pos2", $this->getPositionVariableName("pos2"), true),
            new ExampleInput("@action.form.resultVariableName", "distance", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0], "pos1");
        $this->setPositionVariableName($content[1], "pos2");
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
