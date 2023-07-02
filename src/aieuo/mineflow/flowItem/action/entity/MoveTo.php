<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;

class MoveTo extends FlowItem implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $entity = "",
        string         $position = "",
        private string $speedX = "0.1",
        private string $speedY = "0",
        private string $speedZ = "0.1"
    ) {
        parent::__construct(self::MOVE_TO, FlowItemCategory::ENTITY, [FlowItemPermission::LOOP]);

        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($position);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "position", "speedX", "speedY", "speedZ"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName(), $this->getSpeedX(), $this->getSpeedY(), $this->getSpeedZ()];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName() !== "" and $this->getSpeedX() !== "" and $this->getSpeedY() !== "" and $this->getSpeedZ() !== "";
    }

    public function setSpeedX(string $speedX): void {
        $this->speedX = $speedX;
    }

    public function getSpeedX(): string {
        return $this->speedX;
    }

    public function setSpeedY(string $moveY): void {
        $this->speedY = $moveY;
    }

    public function getSpeedY(): string {
        return $this->speedY;
    }

    public function setSpeedZ(string $speedZ): void {
        $this->speedZ = $speedZ;
    }

    public function getSpeedZ(): string {
        return $this->speedZ;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getOnlineEntity($source);
        $position = $this->getPosition($source);
        $entityPosition = $entity->getLocation();

        $speedX = $this->getFloat($source->replaceVariables($this->getSpeedX()), min: 0);
        $speedY = $this->getFloat($source->replaceVariables($this->getSpeedY()), min: 0);
        $speedZ = $this->getFloat($source->replaceVariables($this->getSpeedZ()), min: 0);

        $dis = $entityPosition->distance($position);
        if ($dis > 1) {
            $x = $speedX * (($position->x - $entityPosition->x) / $dis);
            $y = $speedY * (($position->y - $entityPosition->y) / $dis);
            $z = $speedZ * (($position->z - $entityPosition->z) / $dis);

            $entity->setMotion(new Vector3($x, $y, $z));
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleNumberInput("@action.moveTo.form.speedX", "0.1", $this->getSpeedX()),
            new ExampleNumberInput("@action.moveTo.form.speedY", "0", $this->getSpeedY()),
            new ExampleNumberInput("@action.moveTo.form.speedZ", "0.1", $this->getSpeedZ()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        $this->setSpeedX($content[2]);
        $this->setSpeedY($content[3]);
        $this->setSpeedZ($content[4]);
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName(), $this->getSpeedX(), $this->getSpeedY(), $this->getSpeedZ()];
    }
}
