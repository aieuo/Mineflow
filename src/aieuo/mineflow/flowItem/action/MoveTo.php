<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;

class MoveTo extends FlowItem implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::MOVE_TO;

    protected $name = "action.moveTo.name";
    protected $detail = "action.moveTo.detail";
    protected $detailDefaultReplace = ["entity", "position", "speedX", "speedY", "speedZ"];

    protected $category = Category::ENTITY;
    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $speedX;
    /** @var string */
    private $speedY;
    /** @var string */
    private $speedZ;

    public function __construct(string $entity = "", string $position = "", string $x = "0.1", string $y = "0", string $z = "0.1") {
        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($position);
        $this->setSpeedX($x);
        $this->setSpeedY($y);
        $this->setSpeedZ($z);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        $speedX = $origin->replaceVariables($this->getSpeedX());
        $this->throwIfInvalidNumber($speedX, 0, null);

        $speedY = $origin->replaceVariables($this->getSpeedY());
        $this->throwIfInvalidNumber($speedY, 0, null);

        $speedZ = $origin->replaceVariables($this->getSpeedZ());
        $this->throwIfInvalidNumber($speedZ, 0, null);

        $dis = $entity->distance($position);
        if ($dis > 1) {
            $x = (float)$speedX * (($position->x - $entity->x) / $dis);
            $y = (float)$speedY * (($position->y - $entity->y) / $dis);
            $z = (float)$speedZ * (($position->z - $entity->z) / $dis);

            $entity->setMotion(new Vector3($x, $y, $z));
        }
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new PositionVariableDropdown($variables, $this->getPositionVariableName()),
                new ExampleNumberInput("@action.moveTo.form.speedX", "0.1", $this->getSpeedX()),
                new ExampleNumberInput("@action.moveTo.form.speedY", "0", $this->getSpeedY()),
                new ExampleNumberInput("@action.moveTo.form.speedZ", "0.1", $this->getSpeedZ()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3], $data[4], $data[5]], "cancel" => $data[6]];
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
