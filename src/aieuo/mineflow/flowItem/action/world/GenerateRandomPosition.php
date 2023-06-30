<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GenerateRandomPosition extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionArgument $position1;
    private PositionArgument $position2;
    private StringArgument $resultName;

    public function __construct(string $min = "", string $max = "", string $resultName = "position") {
        parent::__construct(self::GENERATE_RANDOM_POSITION, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position1 = new PositionArgument("min", $min, "@action.form.target.position 1"),
            $this->position2 = new PositionArgument("max", $max, "@action.form.target.position 2"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "position"),
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
        $resultName = $this->resultName->getString($source);

        if ($pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.position.world.different"));
        }

        $x = mt_rand((int)min($pos1->x, $pos2->x), (int)max($pos1->x, $pos2->x));
        $y = mt_rand((int)min($pos1->y, $pos2->y), (int)max($pos1->y, $pos2->y));
        $z = mt_rand((int)min($pos1->z, $pos2->z), (int)max($pos1->z, $pos2->z));
        $rand = new Position($x, $y, $z, $pos1->getWorld());
        $source->addVariable($resultName, new PositionVariable($rand));

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(PositionVariable::class)
        ];
    }
}
