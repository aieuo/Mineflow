<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\math\Vector3;

class MoveTo extends FlowItem implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected string $id = self::MOVE_TO;

    protected string $name = "action.moveTo.name";
    protected string $detail = "action.moveTo.detail";
    protected array $detailDefaultReplace = ["entity", "position", "speedX", "speedY", "speedZ"];

    protected string $category = Category::ENTITY;
    protected int $permission = self::PERMISSION_LEVEL_1;

    private string $speedX;
    private string $speedY;
    private string $speedZ;

    public function __construct(string $entity = "", string $position = "", string $x = "0.1", string $y = "0", string $z = "0.1") {
        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($position);
        $this->setSpeedX($x);
        $this->setSpeedY($y);
        $this->setSpeedZ($z);
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPositionVariableName(), $this->getSpeedX(), $this->getSpeedY(), $this->getSpeedZ()]);
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $position = $this->getPosition($source);

        $speedX = $source->replaceVariables($this->getSpeedX());
        $this->throwIfInvalidNumber($speedX, 0, null);

        $speedY = $source->replaceVariables($this->getSpeedY());
        $this->throwIfInvalidNumber($speedY, 0, null);

        $speedZ = $source->replaceVariables($this->getSpeedZ());
        $this->throwIfInvalidNumber($speedZ, 0, null);

        $dis = $entity->distance($position);
        if ($dis > 1) {
            $x = (float)$speedX * (($position->x - $entity->x) / $dis);
            $y = (float)$speedY * (($position->y - $entity->y) / $dis);
            $z = (float)$speedZ * (($position->z - $entity->z) / $dis);

            $entity->setMotion(new Vector3($x, $y, $z));
        }
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleNumberInput("@action.moveTo.form.speedX", "0.1", $this->getSpeedX()),
            new ExampleNumberInput("@action.moveTo.form.speedY", "0", $this->getSpeedY()),
            new ExampleNumberInput("@action.moveTo.form.speedZ", "0.1", $this->getSpeedZ()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        $this->setSpeedX($content[2]);
        $this->setSpeedY($content[3]);
        $this->setSpeedZ($content[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName(), $this->getSpeedX(), $this->getSpeedY(), $this->getSpeedZ()];
    }
}
