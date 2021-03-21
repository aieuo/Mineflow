<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class SetNameTag extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_NAME;

    protected $name = "action.setNameTag.name";
    protected $detail = "action.setNameTag.detail";
    protected $detailDefaultReplace = ["entity", "name"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $newName;

    public function __construct(string $entity = "", string $newName = "") {
        $this->setEntityVariableName($entity);
        $this->newName = $newName;
    }

    public function setNewName(string $newName): void {
        $this->newName = $newName;
    }

    public function getNewName(): string {
        return $this->newName;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->newName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getNewName()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getNewName());

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.setNameTag.form.name", "aieuo", $this->getNewName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setNewName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getNewName()];
    }
}