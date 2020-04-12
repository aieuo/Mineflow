<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\Player;

class SetNameTag extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_NAME;

    protected $name = "action.setNameTag.name";
    protected $detail = "action.setNameTag.detail";
    protected $detailDefaultReplace = ["entity", "name"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $newName;

    public function __construct(string $targetName = "target", string $newName = "") {
        $this->entityVariableName = $targetName;
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getNewName());

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.setNameTag.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getNewName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setNewName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getNewName()];
    }
}