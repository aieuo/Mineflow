<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use pocketmine\entity\Location;

class CreateHumanEntity extends FlowItem implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected string $name = "action.createHuman.name";
    protected string $detail = "action.createHuman.detail";
    protected array $detailDefaultReplace = ["skin", "pos", "result"];

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $resultName;

    public function __construct(string $name = "", string $pos = "", string $result = "human") {
        parent::__construct(self::CREATE_HUMAN_ENTITY, FlowItemCategory::ENTITY);

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $pos = $this->getPosition($source);

        $resultName = $source->replaceVariables($this->getResultName());

        if (!($pos instanceof Location)) $pos = Location::fromObject($pos, $pos->getWorld());
        $entity = new MineflowHuman($pos, $player->getSkin());
        $entity->spawnToAll();

        $variable = new HumanVariable($entity);
        $source->addVariable($resultName, $variable);
        yield true;
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
            $this->getResultName() => new DummyVariable(EntityVariable::class, $this->getPlayerVariableName())
        ];
    }
}
