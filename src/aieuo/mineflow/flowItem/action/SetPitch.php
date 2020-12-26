<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class SetPitch extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_PITCH;

    protected $name = "action.setPitch.name";
    protected $detail = "action.setPitch.detail";
    protected $detailDefaultReplace = ["entity", "pitch"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $pitch;

    public function __construct(string $entity = "", string $pitch = "") {
        $this->setEntityVariableName($entity);
        $this->pitch = $pitch;
    }

    public function setPitch(string $pitch): self {
        $this->pitch = $pitch;
        return $this;
    }

    public function getPitch(): string {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->pitch !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPitch()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $pitch = $origin->replaceVariables($this->getPitch());
        $this->throwIfInvalidNumber($pitch);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setRotation($entity->getYaw(), (float)$pitch);
        if ($entity instanceof Player) $entity->teleport($entity, $entity->getYaw(), (float)$pitch);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new ExampleNumberInput("@action.setPitch.form.pitch", "180", $this->getPitch(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPitch($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPitch()];
    }
}