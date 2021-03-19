<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Language;

class IsActiveEntity extends FlowItem implements Condition {

    protected $id = self::IS_ACTIVE_ENTITY;

    protected $name = "condition.isActiveEntity.name";
    protected $detail = "condition.isActiveEntity.detail";
    protected $detailDefaultReplace = ["id"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $entityId;

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        yield true;
        return EntityHolder::isActive((int)$id);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@condition.isActiveEntity.form.entityId", "aieuo", $this->getEntityId(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityId($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityId()];
    }
}