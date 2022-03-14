<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\AxisAlignedBBObjectVariable;
use pocketmine\math\AxisAlignedBB;

class CreateAABB extends FlowItem {

    protected string $id = self::CREATE_AABB;

    protected string $name = "action.createAABB.name";
    protected string $detail = "action.createAABB.detail";
    protected array $detailDefaultReplace = ["min x", "min y", "min z", "max x", "max y", "max z", "result"];

    protected string $category = FlowItemCategory::WORLD;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $minX = "",
        private string $minY = "",
        private string $minZ = "",
        private string $maxX = "",
        private string $maxY = "",
        private string $maxZ = "",
        private string $variableName = "aabb"
    ) {
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setMinX(string $minX): void {
        $this->minX = $minX;
    }

    public function getMinX(): string {
        return $this->minX;
    }

    public function setMinY(string $minY): void {
        $this->minY = $minY;
    }

    public function getMinY(): string {
        return $this->minY;
    }

    public function setMinZ(string $minZ): void {
        $this->minZ = $minZ;
    }

    public function getMinZ(): string {
        return $this->minZ;
    }

    public function getMaxX(): string {
        return $this->maxX;
    }

    public function setMaxX(string $maxX): void {
        $this->maxX = $maxX;
    }

    public function getMaxY(): string {
        return $this->maxY;
    }

    public function setMaxY(string $maxY): void {
        $this->maxY = $maxY;
    }

    public function getMaxZ(): string {
        return $this->maxZ;
    }

    public function setMaxZ(string $maxZ): void {
        $this->maxZ = $maxZ;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->minX !== "" and $this->minY !== "" and $this->minZ !== "" and $this->maxX !== "" and $this->maxY !== "" and $this->maxZ !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMinX(), $this->getMinY(), $this->getMinZ(), $this->getMaxX(), $this->getMaxY(), $this->getMaxZ(), $this->getVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $minX = $source->replaceVariables($this->getMinX());
        $minY = $source->replaceVariables($this->getMinY());
        $minZ = $source->replaceVariables($this->getMinZ());
        $maxX = $source->replaceVariables($this->getMaxX());
        $maxY = $source->replaceVariables($this->getMaxY());
        $maxZ = $source->replaceVariables($this->getMaxZ());

        $this->throwIfInvalidNumber($minX);
        $this->throwIfInvalidNumber($minY);
        $this->throwIfInvalidNumber($minZ);
        $this->throwIfInvalidNumber($maxX);
        $this->throwIfInvalidNumber($maxY);
        $this->throwIfInvalidNumber($maxZ);

        $aabb = new AxisAlignedBB(
            min((float)$minX, (float)$maxX),
            min((float)$minY, (float)$maxY),
            min((float)$minZ, (float)$maxZ),
            max((float)$minX, (float)$maxX),
            max((float)$minY, (float)$maxY),
            max((float)$minZ, (float)$maxZ),
        );

        $source->addVariable($name, new AxisAlignedBBObjectVariable($aabb));
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@action.createAABB.form.minX", "0", $this->getMinX(), true),
            new ExampleNumberInput("@action.createAABB.form.minY", "100", $this->getMinY(), true),
            new ExampleNumberInput("@action.createAABB.form.minZ", "16", $this->getMinZ(), true),
            new ExampleNumberInput("@action.createAABB.form.maxX", "10", $this->getMaxX(), true),
            new ExampleNumberInput("@action.createAABB.form.maxY", "200", $this->getMaxY(), true),
            new ExampleNumberInput("@action.createAABB.form.maxZ", "160", $this->getMaxZ(), true),
            new ExampleInput("@action.form.resultVariableName", "area", $this->getVariableName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setMinX($content[0]);
        $this->setMinY($content[1]);
        $this->setMinZ($content[2]);
        $this->setMaxX($content[3]);
        $this->setMaxY($content[4]);
        $this->setMaxZ($content[5]);
        $this->setVariableName($content[6]);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getMinX(),
            $this->getMinY(),
            $this->getMinZ(),
            $this->getMaxX(),
            $this->getMaxY(),
            $this->getMaxZ(),
            $this->getVariableName()
        ];
    }

    public function getAddingVariables(): array {
        $pos1 = $this->getMinX().", ".$this->getMinY().", ".$this->getMinZ();
        $pos2 = $this->getMaxX().", ".$this->getMaxY().", ".$this->getMaxZ();
        $area = "({$pos1}) ~ ({$pos2})";
        return [
            $this->getVariableName() => new DummyVariable(DummyVariable::AXIS_ALIGNED_BB, $area)
        ];
    }
}