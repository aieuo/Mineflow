<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\level\Position;

class InArea extends Condition implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::IN_AREA;

    protected $name = "condition.inArea.name";
    protected $detail = "condition.inArea.detail";
    protected $detailDefaultReplace = ["target", "pos1", "pos2"];

    protected $category = Categories::CATEGORY_CONDITION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;
    protected $returnValueType = self::RETURN_NONE;

    /* @var string */
    private $pos1 = "pos1";
    /* @var string */
    private $pos2 = "pos2";

    public function __construct(string $entity = "target", string $pos1 = "pos1", string $pos2 = "pos2") {
        $this->entityVariableName = $entity;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
    }

    public function getPos1(): string {
        return $this->pos1;
    }

    public function setPos1(string $name) {
        $this->pos1 = $name;
        return $this;
    }

    public function getPosition1(Recipe $origin): ?Position {
        $name = $origin->replaceVariables($this->getPos1());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof PositionObjectVariable)) return null;
        return $variable->getPosition();
    }

    public function getPos2(): string {
        return $this->pos2;
    }

    public function setPos2(string $name) {
        $this->pos2 = $name;
        return $this;
    }

    public function getPosition2(Recipe $origin): ?Position {
        $name = $origin->replaceVariables($this->getPos2());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof PositionObjectVariable)) return null;
        return $variable->getPosition();
    }


    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPos1() !== "" and $this->getPos2() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPos1(), $this->getPos2()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $pos1 = $this->getPosition1($origin);
        $this->throwIfInvalidPosition($pos1);

        $pos2 = $this->getPosition2($origin);
        $this->throwIfInvalidPosition($pos2);

        return $entity->x >= min($pos1->x, $pos2->x) and $entity->x <= max($pos1->x, $pos2->x)
            and $entity->y >= min($pos1->y, $pos2->y) and $entity->y <= max($pos1->y, $pos2->y)
            and $entity->z >= min($pos1->z, $pos2->z) and $entity->z <= max($pos1->z, $pos2->z);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@condition.form.inArea.pos1", Language::get("form.example", ["pos1"]), $default[2] ?? $this->getPos1()),
                new Input("@condition.form.inArea.pos2", Language::get("form.example", ["pos2"]), $default[3] ?? $this->getPos2()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $errors[] = ["@form.insufficient", 3];
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setPos1($content[1]);
        $this->setPos2($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPos1(), $this->getPos2()];
    }
}