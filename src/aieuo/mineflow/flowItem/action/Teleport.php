<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class Teleport extends Action implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::TELEPORT;

    protected $name = "action.teleport.name";
    protected $detail = "action.teleport.detail";
    protected $detailDefaultReplace = ["entity", "position"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;
    protected $returnValueType = self::RETURN_NONE;

    public function __construct(string $name = "target", string $position = "pos") {
        $this->entityVariableName = $name;
        $this->positionVariableName = $position;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPositionVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName() !== "";
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        $entity->teleport($position);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@flowItem.form.target.position", Language::get("form.example", ["pos"]), $default[2] ?? $this->getPositionVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $data[2] = "pos";
        return ["status" => true, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName()];
    }
}
