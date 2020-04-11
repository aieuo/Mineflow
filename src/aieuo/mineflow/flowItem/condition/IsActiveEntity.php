<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class IsActiveEntity extends Condition {

    protected $id = self::IS_ACTIVE_ENTITY;

    protected $name = "condition.isActiveEntity.name";
    protected $detail = "condition.isActiveEntity.detail";
    protected $detailDefaultReplace = ["id"];

    protected $category = Categories::CATEGORY_CONDITION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $entityId = "";

    public function __construct(string $id = "") {
        $this->entityId = $id;
    }

    public function setEntityId(string $id): self {
        $this->entityId = $id;
        return $this;
    }

    public function getEntityId(): string {
        return $this->entityId;
    }

    public function isDataValid(): bool {
        return $this->getEntityId() !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityId()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        return EntityHolder::isActive((int)$id);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.isActiveEntity.form.entityId", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getEntityId()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setEntityId($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityId()];
    }
}