<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use pocketmine\entity\Entity;

class CreateHumanEntity extends FlowItem implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::CREATE_HUMAN_ENTITY;

    protected $name = "action.createHuman.name";
    protected $detail = "action.createHuman.detail";
    protected $detailDefaultReplace = ["skin", "pos", "result"];

    protected $category = Category::ENTITY;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $pos = "", string $result = "human") {
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

    public function execute(Recipe $origin) {
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
        yield true;
        return $this->getResultName();
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.createHuman.form.skin", "target", $this->getPlayerVariableName(), true),
                new PositionVariableDropdown($variables, $this->getPositionVariableName()),
                new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPositionVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::ENTITY, $this->getPlayerVariableName())];
    }
}