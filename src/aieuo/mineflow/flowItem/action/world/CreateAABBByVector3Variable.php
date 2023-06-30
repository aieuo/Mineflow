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
use SOFe\AwaitGenerator\Await;

class CreateAABBByVector3Variable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private Vector3Argument $pos1;
    private Vector3Argument $pos2;
    private StringArgument $variableName;

    public function __construct(string $pos1 = "", string $pos2 = "", string $variableName = "area") {
        parent::__construct(self::CREATE_AABB_BY_VECTOR3_VARIABLE, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->pos1 = new Vector3Argument("pos1", $pos1, "@action.createAABBByVector3Variable.form.pos1"),
            $this->pos2 = new Vector3Argument("pos2", $pos2, "@action.createAABBByVector3Variable.form.pos1"),
            $this->variableName = new StringArgument("result", $variableName, "@action.form.resultVariableName", example: "area"),
        ]);
    }

    public function getPos1(): Vector3Argument {
        return $this->pos1;
    }

    public function getPos2(): Vector3Argument {
        return $this->pos2;
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $pos1 = $this->pos1->getVector3($source);
        $pos2 = $this->pos2->getVector3($source);

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
        return $this->variableName->get();
    }

    public function getAddingVariables(): array {
        $pos1 = $this->pos1->get();
        $pos2 = $this->pos2->get();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            $this->variableName->get() => new DummyVariable(AxisAlignedBB::class, $area)
        ];
    }
}
