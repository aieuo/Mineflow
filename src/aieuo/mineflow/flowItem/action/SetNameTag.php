<?php

namespace aieuo\mineflow\flowItem\action;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\Player;

class SetNameTag extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_NAME;

    protected $name = "action.setNameTag.name";
    protected $detail = "action.setNameTag.detail";
    protected $detailDefaultReplace = ["entity", "name"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $newName;

    public function __construct(string $entity = "target", string $newName = "") {
        $this->setEntityVariableName($entity);
        $this->newName = $newName;
    }

    public function setNewName(string $newName) {
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getNewName());

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.entity", "target", $default[1] ?? $this->getEntityVariableName(), true),
                new ExampleInput("@action.setNameTag.form.name", "aieuo", $default[2] ?? $this->getNewName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
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