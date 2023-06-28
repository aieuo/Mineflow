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

    private NumberArgument $minX;
    private NumberArgument $minY;
    private NumberArgument $minZ;
    private NumberArgument $maxX;
    private NumberArgument $maxY;
    private NumberArgument $maxZ;
    private StringArgument $variableName;

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
            $this->minX = new NumberArgument("min x", $minX, "@action.createAABB.form.minX", "0"),
            $this->minY = new NumberArgument("min y", $minY, "@action.createAABB.form.minY", "100"),
            $this->minZ = new NumberArgument("min z", $minZ, "@action.createAABB.form.minZ", "16"),
            $this->maxX = new NumberArgument("max x", $maxX, "@action.createAABB.form.maxX", "10"),
            $this->maxY = new NumberArgument("max y", $maxY, "@action.createAABB.form.maxY", "200"),
            $this->maxZ = new NumberArgument("max z", $maxZ, "@action.createAABB.form.maxZ", "160"),
            $this->variableName = new StringArgument("result", $variableName, "@action.form.resultVariableName", "area"),
        ]);
    }

    public function getMinX(): NumberArgument {
        return $this->minX;
    }

    public function getMinY(): NumberArgument {
        return $this->minY;
    }

    public function getMinZ(): NumberArgument {
        return $this->minZ;
    }

    public function getMaxX(): NumberArgument {
        return $this->maxX;
    }

    public function getMaxY(): NumberArgument {
        return $this->maxY;
    }

    public function getMaxZ(): NumberArgument {
        return $this->maxZ;
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $minX = $this->minX->getFloat($source);
        $minY = $this->minY->getFloat($source);
        $minZ = $this->minZ->getFloat($source);
        $maxX = $this->maxX->getFloat($source);
        $maxY = $this->maxY->getFloat($source);
        $maxZ = $this->maxZ->getFloat($source);

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
        return $this->variableName->get();
    }

    public function getAddingVariables(): array {
        $pos1 = $this->minX->get().", ".$this->minY->get().", ".$this->minZ->get();
        $pos2 = $this->maxX->get().", ".$this->maxY->get().", ".$this->maxZ->get();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            $this->variableName->get() => new DummyVariable(AxisAlignedBBVariable::class, $area)
        ];
    }
}
