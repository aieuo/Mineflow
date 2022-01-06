<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;

abstract class CheckEntityStateById extends FlowItem implements Condition {

    protected array $detailDefaultReplace = ["id"];

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::ENTITY,
        private string $entityId = "",
    ) {
        parent::__construct($id, $category);
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
