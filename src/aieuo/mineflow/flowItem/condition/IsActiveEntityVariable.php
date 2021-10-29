<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class IsActiveEntityVariable extends FlowItem implements Condition, EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::IS_ACTIVE_ENTITY_VARIABLE;

    protected string $name = "condition.isActiveEntityVariable.name";
    protected string $detail = "condition.isActiveEntityVariable.detail";
    protected array $detailDefaultReplace = ["entity"];

    protected string $category = Category::ENTITY;

    public function __construct(string $entity = "") {
        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== null;
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getOnlineEntity($source);

        yield FlowItemExecutor::CONTINUE;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables),
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