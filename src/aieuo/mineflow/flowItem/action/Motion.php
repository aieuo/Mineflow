<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\math\Vector3;

class Motion extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::MOTION;

    protected $name = "action.motion.name";
    protected $detail = "action.motion.detail";
    protected $detailDefaultReplace = ["entity", "x", "y", "z"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $x = "0";
    /** @var string */
    private $y = "0";
    /** @var string */
    private $z = "0";

    public function __construct(string $entity = "", string $x = "0", string $y = "0", string $z = "0") {
        $this->setEntityVariableName($entity);
        $this->setPosition($x, $y, $z);
    }

    public function setPosition(string $x, string $y, string $z): self {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    public function getPosition(): array {
        return [$this->x, $this->y, $this->z];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, array_merge([$this->getEntityVariableName()], $this->getPosition()));
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $motions = array_map(function ($value) use ($source) {
            $v = $source->replaceVariables($value);
            $this->throwIfInvalidNumber($v);
            return $v;
        }, $this->getPosition());

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $motion = new Vector3((float)$motions[0], (float)$motions[1], (float)$motions[2]);
        $entity->setMotion($motion);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.motion.form.x", "2", $this->x, true),
            new ExampleNumberInput("@action.motion.form.y", "3", $this->y, true),
            new ExampleNumberInput("@action.motion.form.z", "4", $this->z, true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPosition($content[1], $content[2], $content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getEntityVariableName()], $this->getPosition());
    }
}