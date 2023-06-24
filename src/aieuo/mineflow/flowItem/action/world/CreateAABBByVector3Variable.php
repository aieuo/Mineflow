<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\Vector3Argument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;
use SOFe\AwaitGenerator\Await;

class CreateAABBByVector3Variable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private Vector3Argument $pos1;
    private Vector3Argument $pos2;

    public function __construct(
        string         $pos1 = "",
        string         $pos2 = "",
        private string $variableName = "area"
    ) {
        parent::__construct(self::CREATE_AABB_BY_VECTOR3_VARIABLE, FlowItemCategory::WORLD);

        $this->pos1 = new Vector3Argument("pos1", $pos1, "@action.createAABBByVector3Variable.form.pos1");
        $this->pos2 = new Vector3Argument("pos2", $pos2, "@action.createAABBByVector3Variable.form.pos1");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->pos1->getName(), $this->pos2->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->pos1->get(), $this->pos2->get(), $this->getVariableName()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->pos1->isNotEmpty() and $this->pos2->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
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
        return $this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->pos1->createFormElement($variables),
            $this->pos2->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "area", $this->getVariableName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->pos1->set($content[0]);
        $this->pos2->set($content[1]);
        $this->setVariableName($content[2]);
    }

    public function serializeContents(): array {
        return [
            $this->pos1->get(),
            $this->pos2->get(),
            $this->getVariableName()
        ];
    }

    public function getAddingVariables(): array {
        $pos1 = $this->pos1->get();
        $pos2 = $this->pos2->get();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            $this->getVariableName() => new DummyVariable(AxisAlignedBB::class, $area)
        ];
    }
}
