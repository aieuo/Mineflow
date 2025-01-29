<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\Server;
use pocketmine\world\Position;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class CreatePositionVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(float $x = 0, float $y = 0, float $z = 0, string $world = "{target.world.name}", string $variableName = "pos") {
        parent::__construct(self::CREATE_POSITION_VARIABLE, FlowItemCategory::WORLD);

        $this->setArguments([
            StringArgument::create("position", $variableName, "@action.form.resultVariableName")->example("pos"),
            NumberArgument::create("x", $x)->example("0"),
            NumberArgument::create("y", $y)->example("100"),
            NumberArgument::create("z", $z)->example("16"),
            StringArgument::create("world", $world)->example("{target.level}"),
        ]);
    }

    public function getVariableName(): StringArgument {
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

    public function getWorld(): StringArgument {
        return $this->getArgument("world");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $x = $this->getX()->getFloat($source);
        $y = $this->getY()->getFloat($source);
        $z = $this->getZ()->getFloat($source);
        $levelName = $this->getWorld()->getString($source);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($levelName);

        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $position = new Position($x, $y, $z, $level);

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function getAddingVariables(): array {
        $pos = $this->getX().", ".$this->getY().", ".$this->getZ().", ".$this->getWorld();
        return [
            (string)$this->getVariableName() => new DummyVariable(PositionVariable::class, $pos)
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getX(),
                $this->getY(),
                $this->getZ(),
                $this->getWorld(),
                $this->getVariableName(),
            ]),
        ];
    }
}