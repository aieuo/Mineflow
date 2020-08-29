<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use pocketmine\entity\Entity;

class CreateHumanEntity extends Action implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::CREATE_HUMAN_ENTITY;

    protected $name = "action.createHuman.name";
    protected $detail = "action.createHuman.detail";
    protected $detailDefaultReplace = ["skin", "pos", "result"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $resultName;

    public function __construct(string $name = "target", string $pos = "pos", string $result = "human") {
        $this->setPlayerVariableName($name);
        $this->setPositionVariableName($pos);
        $this->resultName = $result;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getPositionVariableName() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPositionVariableName(), $this->getResultName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $pos = $this->getPosition($origin);
        $this->throwIfInvalidPosition($pos);

        $resultName = $origin->replaceVariables($this->getResultName());

        $nbt = Entity::createBaseNBT($pos);
        $nbt->setTag($player->namedtag->getCompoundTag("Skin"));

        $entity = new MineflowHuman($pos->getLevel(), $nbt);
        $entity->spawnToAll();

        $variable = new HumanObjectVariable($entity, $resultName);
        $origin->addVariable($variable);
        return false;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.createHuman.form.skin", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new ExampleInput("@flowItem.form.target.position", "pos", $default[2] ?? $this->getPositionVariableName(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "entity", $default[3] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPositionVariableName(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->getResultName();
    }
}