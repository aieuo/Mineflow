<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class UnsetImmobile extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::UNSET_IMMOBILE;

    protected $name = "action.unsetImmobile.name";
    protected $detail = "action.unsetImmobile.detail";
    protected $detailDefaultReplace = ["entity"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;
    protected $returnValueType = self::RETURN_NONE;

    public function __construct(string $name = "target") {
        $this->entityVariableName = $name;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $target->setImmobile(false);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "target";
        return ["status" => true, "contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();

        $this->setEntityVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName()];
    }
}
