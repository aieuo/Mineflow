<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\Vector3Argument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class CreateAABBByVector3Variable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $pos1 = "", string $pos2 = "", string $variableName = "area") {
        parent::__construct(self::CREATE_AABB_BY_VECTOR3_VARIABLE, FlowItemCategory::WORLD);

        $this->setArguments([
            Vector3Argument::create("pos1", $pos1, "@action.createAABBByVector3Variable.form.pos1"),
            Vector3Argument::create("pos2", $pos2, "@action.createAABBByVector3Variable.form.pos1"),
            StringArgument::create("result", $variableName, "@action.form.resultVariableName")->example("area"),
        ]);
    }

    public function getPos1(): Vector3Argument {
        return $this->getArgument("pos1");
    }

    public function getPos2(): Vector3Argument {
        return $this->getArgument("pos2");
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $pos1 = $this->getPos1()->getVector3($source);
        $pos2 = $this->getPos2()->getVector3($source);

        $aabb = new AxisAlignedBB(
            min((float)$pos1->x, (float)$pos2->x),
            min((float)$pos1->y, (float)$pos2->y),
            min((float)$pos1->z, (float)$pos2->z),
            max((float)$pos1->x, (float)$pos2->x),
            max((float)$pos1->y, (float)$pos2->y),
            max((float)$pos1->z, (float)$pos2->z),
        );

        $source->addVariable($name, new AxisAlignedBBVariable($aabb));

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function getAddingVariables(): array {
        $pos1 = $this->getPos1();
        $pos2 = $this->getPos2();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            (string)$this->getVariableName() => new DummyVariable(AxisAlignedBB::class, $area)
        ];
    }
}