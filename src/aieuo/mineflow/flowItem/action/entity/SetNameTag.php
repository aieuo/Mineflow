<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class SetNameTag extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::SET_NAME;

    protected string $name = "action.setNameTag.name";
    protected string $detail = "action.setNameTag.detail";
    protected array $detailDefaultReplace = ["entity", "name"];

    protected string $category = Category::ENTITY;

    private string $newName;

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
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getNewName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getNewName());

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);
        yield FlowItemExecutor::CONTINUE;
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