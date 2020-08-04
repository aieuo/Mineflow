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
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\level\Position;

class InArea extends Condition implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::IN_AREA;

    protected $name = "condition.inArea.name";
    protected $detail = "condition.inArea.detail";
    protected $detailDefaultReplace = ["target", "pos1", "pos2"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function __construct(string $entity = "target", string $pos1 = "pos1", string $pos2 = "pos2") {
        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName("pos1") !== "" and $this->getPositionVariableName("pos2") !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $pos1 = $this->getPosition($origin, "pos1");
        $this->throwIfInvalidPosition($pos1);

        $pos2 = $this->getPosition($origin, "pos2");
        $this->throwIfInvalidPosition($pos2);

        $pos = $entity->floor();

        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@condition.inArea.form.pos1", Language::get("form.example", ["pos1"]), $default[2] ?? $this->getPositionVariableName("pos1")),
                new Input("@condition.inArea.form.pos2", Language::get("form.example", ["pos2"]), $default[3] ?? $this->getPositionVariableName("pos2")),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $errors[] = ["@form.insufficient", 3];
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1], "pos1");
        $this->setPositionVariableName($content[2], "pos2");
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")];
    }
}