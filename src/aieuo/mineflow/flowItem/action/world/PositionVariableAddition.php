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
use SOFe\AwaitGenerator\Await;

class PositionVariableAddition extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionArgument $position;
    private NumberArgument $x;
    private NumberArgument $y;
    private NumberArgument $z;
    private StringArgument $resultName;

    public function __construct(string $position = "pos", float $x = null, float $y = null, float $z = null, string $resultName = "pos") {
        parent::__construct(self::POSITION_VARIABLE_ADDITION, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->x = new NumberArgument("x", $x, example: "0"),
            $this->y = new NumberArgument("y", $y, example: "100"),
            $this->z = new NumberArgument("z", $z, example: "16"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "pos"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getX(): NumberArgument {
        return $this->x;
    }

    public function getY(): NumberArgument {
        return $this->y;
    }

    public function getZ(): NumberArgument {
        return $this->z;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos = $this->position->getPosition($source);

        $x = $this->x->getFloat($source);
        $y = $this->y->getFloat($source);
        $z = $this->z->getFloat($source);
        $name = $this->resultName->getString($source);

        $position = Position::fromObject($pos->add($x, $y, $z), $pos->getWorld());

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        $desc = $this->position->get()." + (".$this->x->get().",".$this->y->get().",".$this->z->get().")";
        return [
            $this->resultName->get() => new DummyVariable(PositionVariable::class, $desc)
        ];
    }
}
