<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class UnsetImmobile extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::UNSET_IMMOBILE;

    protected $name = "action.unsetImmobile.name";
    protected $detail = "action.unsetImmobile.detail";
    protected $detailDefaultReplace = ["entity"];

    protected $category = Category::ENTITY;

    public function __construct(string $entity = "") {
        $this->setEntityVariableName($entity);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setImmobile(false);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName()];
    }
}
