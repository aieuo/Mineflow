<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Language;

class IsActiveEntity extends FlowItem implements Condition {

    protected string $id = self::IS_ACTIVE_ENTITY;

    protected string $name = "condition.isActiveEntity.name";
    protected string $detail = "condition.isActiveEntity.detail";
    protected array $detailDefaultReplace = ["id"];

    protected string $category = Category::ENTITY;

    private string $entityId;

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
        return Language::get($this->detail, [$this->getEntityId()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        FlowItemExexutor::CONTINUE;
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