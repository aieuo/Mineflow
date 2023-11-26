<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;
use SOFe\AwaitGenerator\Await;

class CreateAABB extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string $minX = "",
        string $minY = "",
        string $minZ = "",
        string $maxX = "",
        string $maxY = "",
        string $maxZ = "",
        string $variableName = "aabb"
    ) {
        parent::__construct(self::CREATE_AABB, FlowItemCategory::WORLD);

        $this->setArguments([
            NumberArgument::create("min x", $minX, "@action.createAABB.form.minX")->example("0"),
            NumberArgument::create("min y", $minY, "@action.createAABB.form.minY")->example("100"),
            NumberArgument::create("min z", $minZ, "@action.createAABB.form.minZ")->example("16"),
            NumberArgument::create("max x", $maxX, "@action.createAABB.form.maxX")->example("10"),
            NumberArgument::create("max y", $maxY, "@action.createAABB.form.maxY")->example("200"),
            NumberArgument::create("max z", $maxZ, "@action.createAABB.form.maxZ")->example("160"),
            StringArgument::create("result", $variableName, "@action.form.resultVariableName")->example("area"),
        ]);
    }

    public function getMinX(): NumberArgument {
        return $this->getArguments()[0];
    }

    public function getMinY(): NumberArgument {
        return $this->getArguments()[1];
    }

    public function getMinZ(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getMaxX(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function getMaxY(): NumberArgument {
        return $this->getArguments()[4];
    }

    public function getMaxZ(): NumberArgument {
        return $this->getArguments()[5];
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[6];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $minX = $this->getMinX()->getFloat($source);
        $minY = $this->getMinY()->getFloat($source);
        $minZ = $this->getMinZ()->getFloat($source);
        $maxX = $this->getMaxX()->getFloat($source);
        $maxY = $this->getMaxY()->getFloat($source);
        $maxZ = $this->getMaxZ()->getFloat($source);

        $aabb = new AxisAlignedBB(
            min($minX, $maxX),
            min($minY, $maxY),
            min($minZ, $maxZ),
            max($minX, $maxX),
            max($minY, $maxY),
            max($minZ, $maxZ),
        );

        $source->addVariable($name, new AxisAlignedBBVariable($aabb));

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function getAddingVariables(): array {
        $pos1 = $this->getMinX().", ".$this->getMinY().", ".$this->getMinZ();
        $pos2 = $this->getMaxX().", ".$this->getMaxY().", ".$this->getMaxZ();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            (string)$this->getVariableName() => new DummyVariable(AxisAlignedBBVariable::class, $area)
        ];
    }
}
