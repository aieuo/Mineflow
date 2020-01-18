<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
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

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $id = $origin->replaceVariables($this->getEntityId());
        if (!is_numeric($id)) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("flowItem.error.notNumber")]), $target);
            return null;
        }

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
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Condition {
        if (!isset($content[0])) return null;
        $this->setEntityId($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityId()];
    }
}