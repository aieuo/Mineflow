<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\FormAPI\element\Toggle;

class IsActiveEntity extends Condition {

    protected $id = self::IS_ACTIVE_ENTITY;

    protected $name = "@condition.isActiveEntity.name";
    protected $description = "@condition.isActiveEntity.description";
    protected $detail = "condition.isActiveEntity.detail";

    protected $category = Categories::CATRGORY_CONDITION_ENTITY;

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

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            if ($target instanceof Player) $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            else Server::getInstance()->getLogger()->info(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }


        $id = $this->getEntityId();
        if ($origin instanceof Recipe) {
            $id = $origin->replaceVariables($id);
        }
        if (!is_numeric($id)) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        }

        return EntityHolder::getInstance()->isActive((int)$id);
    }

    public function getEditForm(array $default = [], array $errors = []) {
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

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[0])) return null;
        $this->setEntityId($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityId()];
    }
}