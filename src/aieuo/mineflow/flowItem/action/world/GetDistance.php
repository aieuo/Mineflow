<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GetDistance extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $pos1 = "", string $pos2 = "", string $resultName = "distance") {
        parent::__construct(self::GET_DISTANCE, FlowItemCategory::WORLD);

        $this->setArguments([
            new PositionArgument("pos1", $pos1),
            new PositionArgument("pos2", $pos2),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "distance"),
        ]);
    }

    public function getPosition1(): PositionArgument {
        return $this->getArguments()[0];
    }

    public function getPosition2(): PositionArgument {
        return $this->getArguments()[1];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->getPosition1()->getPosition($source);
        $pos2 = $this->getPosition2()->getPosition($source);
        $result = $this->getResultName()->getString($source);

        $distance = $pos1->distance($pos2);

        $source->addVariable($result, new NumberVariable($distance));

        yield Await::ALL;
        return $distance;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
