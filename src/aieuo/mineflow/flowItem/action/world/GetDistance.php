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

    private PositionArgument $position1;
    private PositionArgument $position2;
    private StringArgument $resultName;

    public function __construct(string $pos1 = "", string $pos2 = "", string $resultName = "distance") {
        parent::__construct(self::GET_DISTANCE, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position1 = new PositionArgument("pos1", $pos1),
            $this->position2 = new PositionArgument("pos2", $pos2),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "distance"),
        ]);
    }

    public function getPosition1(): PositionArgument {
        return $this->position1;
    }

    public function getPosition2(): PositionArgument {
        return $this->position2;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);
        $result = $this->resultName->getString($source);

        $distance = $pos1->distance($pos2);

        $source->addVariable($result, new NumberVariable($distance));

        yield Await::ALL;
        return $distance;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
