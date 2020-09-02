<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

class GetEntity extends FlowItem {

    protected $id = self::GET_ENTITY;

    protected $name = "action.getEntity.name";
    protected $detail = "action.getEntity.detail";
    protected $detailDefaultReplace = ["id", "result"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $entityId;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $result = "entity") {
        $this->entityId = $name;
        $this->resultName = $result;
    }

    public function setKey(string $name): self {
        $this->entityId = $name;
        return $this;
    }

    public function getKey(): string {
        return $this->entityId;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getKey() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getKey(), $this->getResultName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getKey());
        $resultName = $origin->replaceVariables($this->getResultName());

        $this->throwIfInvalidNumber($id, 0);

        $entity = EntityHolder::findEntity((int)$id);
        if ($entity instanceof Player) {
            $result = new PlayerObjectVariable($entity, $resultName, $entity->getName());
            $origin->addVariable($result);
            return true;
        }
        if ($entity instanceof Entity) {
            $result = new EntityObjectVariable($entity, $resultName, $entity->getNameTag());
            $origin->addVariable($result);
            return true;
        }
        $origin->addVariable(new MapVariable([], $resultName)); // TODO: .
        yield true;
        return false;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.getEntity.form.target", "aieuo", $default[1] ?? $this->getKey(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "entity", $default[2] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setKey($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->getResultName();
    }
}