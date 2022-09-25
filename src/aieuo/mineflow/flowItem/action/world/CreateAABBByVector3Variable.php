<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\Vector3FlowItem;
use aieuo\mineflow\flowItem\base\Vector3FlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\Vector3VariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;

class CreateAABBByVector3Variable extends FlowItem implements Vector3FlowItem {
    use Vector3FlowItemTrait;

    protected string $id = self::CREATE_AABB_BY_VECTOR3_VARIABLE;

    protected string $name = "action.createAABBByPositionVariable.name";
    protected string $detail = "action.createAABBByPositionVariable.detail";
    protected array $detailDefaultReplace = ["pos1", "pos2", "result"];

    protected string $category = FlowItemCategory::WORLD;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $pos1 = "",
        string         $pos2 = "",
        private string $variableName = "area"
    ) {
        $this->setVector3VariableName($pos1, "pos1");
        $this->setVector3VariableName($pos2, "pos2");
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->getVector3VariableName("pos1") !== "" and $this->getVector3VariableName("pos2") !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVector3VariableName("pos1"), $this->getVector3VariableName("pos2"), $this->getVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $pos1 = $this->getVector3($source, "pos1");
        $pos2 = $this->getVector3($source, "pos2");

        $aabb = new AxisAlignedBB(
            min((float)$pos1->x, (float)$pos2->x),
            min((float)$pos1->y, (float)$pos2->y),
            min((float)$pos1->z, (float)$pos2->z),
            max((float)$pos1->x, (float)$pos2->x),
            max((float)$pos1->y, (float)$pos2->y),
            max((float)$pos1->z, (float)$pos2->z),
        );

        $source->addVariable($name, new AxisAlignedBBVariable($aabb));
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new Vector3VariableDropdown($variables, $this->getVector3VariableName("pos1"), "@action.createAABBByPositionVariable.form.pos1"),
            new Vector3VariableDropdown($variables, $this->getVector3VariableName("pos2"), "@action.createAABBByPositionVariable.form.pos2"),
            new ExampleInput("@action.form.resultVariableName", "area", $this->getVariableName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVector3VariableName($content[0], "pos1");
        $this->setVector3VariableName($content[1], "pos2");
        $this->setVariableName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getVector3VariableName("pos1"),
            $this->getVector3VariableName("pos2"),
            $this->getVariableName()
        ];
    }

    public function getAddingVariables(): array {
        $pos1 = $this->getVector3VariableName("pos1");
        $pos2 = $this->getVector3VariableName("pos2");
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            $this->getVariableName() => new DummyVariable(AxisAlignedBB::class, $area)
        ];
    }
}
