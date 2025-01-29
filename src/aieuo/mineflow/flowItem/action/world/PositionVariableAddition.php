<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class PositionVariableAddition extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $position = "pos", float $x = null, float $y = null, float $z = null, string $resultName = "pos") {
        parent::__construct(self::POSITION_VARIABLE_ADDITION, FlowItemCategory::WORLD);

        $this->setArguments([
            PositionArgument::create("position", $position),
            NumberArgument::create("x", $x)->example("0"),
            NumberArgument::create("y", $y)->example("100"),
            NumberArgument::create("z", $z)->example("16"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("pos"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
    }

    public function getX(): NumberArgument {
        return $this->getArgument("x");
    }

    public function getY(): NumberArgument {
        return $this->getArgument("y");
    }

    public function getZ(): NumberArgument {
        return $this->getArgument("z");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos = $this->getPosition()->getPosition($source);

        $x = $this->getX()->getFloat($source);
        $y = $this->getY()->getFloat($source);
        $z = $this->getZ()->getFloat($source);
        $name = $this->getResultName()->getString($source);

        $position = Position::fromObject($pos->add($x, $y, $z), $pos->getWorld());

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        $desc = $this->getPosition()." + (".$this->getX().",".$this->getY().",".$this->getZ().")";
        return [
            (string)$this->getResultName() => new DummyVariable(PositionVariable::class, $desc)
        ];
    }
}