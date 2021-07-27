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
use pocketmine\entity\Living;

class ClearAllEffect extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::CLEAR_ALL_EFFECT;

    protected $name = "action.clearAllEffect.name";
    protected $detail = "action.clearAllEffect.detail";
    protected $detailDefaultReplace = ["entity"];

    protected $category = Category::ENTITY;

    public function __construct(string $entity = "") {
        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        if ($entity instanceof Living) {
            $entity->removeAllEffects();
        }
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