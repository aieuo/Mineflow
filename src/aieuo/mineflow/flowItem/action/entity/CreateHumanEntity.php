<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use pocketmine\entity\Entity;

class CreateHumanEntity extends FlowItem implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected string $id = self::CREATE_HUMAN_ENTITY;

    protected string $name = "action.createHuman.name";
    protected string $detail = "action.createHuman.detail";
    protected array $detailDefaultReplace = ["skin", "pos", "result"];

    protected string $category = Category::ENTITY;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $resultName;

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
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPositionVariableName(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $pos = $this->getPosition($source);

        $resultName = $source->replaceVariables($this->getResultName());

        $nbt = Entity::createBaseNBT($pos);
        $nbt->setTag($player->namedtag->getCompoundTag("Skin"));

        $entity = new MineflowHuman($pos->getLevel(), $nbt);
        $entity->spawnToAll();

        $variable = new HumanObjectVariable($entity);
        $source->addVariable($resultName, $variable);
        yield FlowItemExecutor::CONTINUE;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createHuman.form.skin", "target", $this->getPlayerVariableName(), true),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ];
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
        return [
            $this->getResultName() => new DummyVariable(EntityObjectVariable::class, $this->getPlayerVariableName())
        ];
    }
}