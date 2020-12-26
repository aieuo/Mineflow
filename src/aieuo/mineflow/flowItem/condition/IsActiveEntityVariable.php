<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class IsActiveEntityVariable extends FlowItem implements Condition, EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::IS_ACTIVE_ENTITY_VARIABLE;

    protected $name = "condition.isActiveEntityVariable.name";
    protected $detail = "condition.isActiveEntityVariable.detail";
    protected $detailDefaultReplace = ["entity"];

    protected $category = Category::ENTITY;

    public function __construct(string $entity = "") {
        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        yield true;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName()];
    }
}