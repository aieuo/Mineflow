<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GetDistance extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private PositionPlaceholder $position1;
    private PositionPlaceholder $position2;

    public function __construct(
        string         $pos1 = "",
        string         $pos2 = "",
        private string $resultName = "distance"
    ) {
        parent::__construct(self::GET_DISTANCE, FlowItemCategory::WORLD);

        $this->position1 = new PositionPlaceholder("pos1", $pos1);
        $this->position2 = new PositionPlaceholder("pos2", $pos2);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position1->getName(), $this->position2->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->position1->get(), $this->position2->get(), $this->getResultName()];
    }

    public function getPosition1(): PositionPlaceholder {
        return $this->position1;
    }

    public function getPosition2(): PositionPlaceholder {
        return $this->position2;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->position1->isNotEmpty() and $this->position2->isNotEmpty() and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);
        $result = $source->replaceVariables($this->getResultName());

        $distance = $pos1->distance($pos2);

        $source->addVariable($result, new NumberVariable($distance));

        yield Await::ALL;
        return $distance;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position1->createFormElement($variables),
            $this->position2->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "distance", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position1->set($content[0]);
        $this->position2->set($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->position1->get(), $this->position2->get(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
