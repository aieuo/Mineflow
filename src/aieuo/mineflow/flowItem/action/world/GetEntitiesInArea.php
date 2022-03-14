<?php

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\AxisAlignedBBFlowItem;
use aieuo\mineflow\flowItem\base\AxisAlignedBBFlowItemTrait;
use aieuo\mineflow\flowItem\base\WorldFlowItem;
use aieuo\mineflow\flowItem\base\WorldFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\AxisAlignedBBVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use pocketmine\entity\Entity;
use function array_map;

class GetEntitiesInArea extends FlowItem implements AxisAlignedBBFlowItem, WorldFlowItem {
    use AxisAlignedBBFlowItemTrait, WorldFlowItemTrait;

    protected string $id = self::GET_ENTITIES_IN_AREA;

    protected string $name = "action.getEntitiesInArea.name";
    protected string $detail = "action.getEntitiesInArea.detail";
    protected array $detailDefaultReplace = ["aabb", "world", "result"];

    protected string $category = FlowItemCategory::WORLD;

    public function __construct(
        string         $aabb = "",
        string         $worldName = "",
        private string $resultName = "entities"
    ) {
        $this->setAxisAlignedBBVariableName($aabb);
        $this->setWorldVariableName($worldName);
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function isDataValid(): bool {
        return $this->getAxisAlignedBBVariableName() !== "" and $this->getWorldVariableName() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getAxisAlignedBBVariableName(), $this->getWorldVariableName(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $aabb = $this->getAxisAlignedBB($source);
        $world = $this->getWorld($source);
        $result = $source->replaceVariables($this->getResultName());

        $entities = $this->filterEntities($world->getNearbyEntities($aabb));
        $variable = new ListVariable(array_map(fn(Entity $entity) => EntityObjectVariable::fromObject($entity), $entities));
        $source->addVariable($result, $variable);

        yield true;
        return $this->getResultName();
    }

    /**
     * @param Entity[] $entities
     * @return Entity[]
     */
    protected function filterEntities(array $entities): array {
        return $entities;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new AxisAlignedBBVariableDropdown($variables, $this->getAxisAlignedBBVariableName()),
            new WorldVariableDropdown($variables, $this->getWorldVariableName()),
            new ExampleInput("@action.form.resultVariableName", "entities", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setAxisAlignedBBVariableName($content[0]);
        $this->setWorldVariableName($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAxisAlignedBBVariableName(), $this->getWorldVariableName(), $this->getResultName()];
    }
}