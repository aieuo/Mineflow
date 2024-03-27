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

    public function __construct(string $min = "", string $max = "", string $resultName = "position") {
        parent::__construct(self::GENERATE_RANDOM_POSITION, FlowItemCategory::WORLD);

        $this->setArguments([
            PositionArgument::create("min", $min, "@action.form.target.position 1"),
            PositionArgument::create("max", $max, "@action.form.target.position 2"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("position"),
        ]);
    }

    public function getPosition1(): PositionArgument {
        return $this->getArgument("min");
    }

    public function getPosition2(): PositionArgument {
        return $this->getArgument("max");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->getPosition1()->getPosition($source);
        $pos2 = $this->getPosition2()->getPosition($source);
        $resultName = $this->getResultName()->getString($source);

        if ($pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.position.world.different"));
        }

        $x = mt_rand((int)min($pos1->x, $pos2->x), (int)max($pos1->x, $pos2->x));
        $y = mt_rand((int)min($pos1->y, $pos2->y), (int)max($pos1->y, $pos2->y));
        $z = mt_rand((int)min($pos1->z, $pos2->z), (int)max($pos1->z, $pos2->z));
        $rand = new Position($x, $y, $z, $pos1->getWorld());
        $source->addVariable($resultName, new PositionVariable($rand));

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(PositionVariable::class)
        ];
    }
}
