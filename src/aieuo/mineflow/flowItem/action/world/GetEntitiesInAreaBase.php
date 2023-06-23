<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\WorldFlowItem;
use aieuo\mineflow\flowItem\base\WorldFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\AxisAlignedBBPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use SOFe\AwaitGenerator\Await;
use function array_map;

abstract class GetEntitiesInAreaBase extends FlowItem implements WorldFlowItem {
    use WorldFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private AxisAlignedBBPlaceholder $aabb;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::WORLD,
        string         $aabb = "",
        string         $worldName = "",
        private string $resultName = "entities"
    ) {
        parent::__construct($id, $category);

        $this->aabb = new AxisAlignedBBPlaceholder("aabb", $aabb);
        $this->setWorldVariableName($worldName);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->aabb->getName(), "world", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->aabb->get(), $this->getWorldVariableName(), $this->getResultName()];
    }

    public function getAxisAlignedBB(): AxisAlignedBBPlaceholder {
        return $this->aabb;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function isDataValid(): bool {
        return $this->aabb->isNotEmpty() and $this->getWorldVariableName() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $aabb = $this->aabb->getAxisAlignedBB($source);
        $world = $this->getWorld($source);
        $result = $source->replaceVariables($this->getResultName());

        $entities = $this->filterEntities($world->getNearbyEntities($aabb));
        $variable = new ListVariable(array_map(fn(Entity $entity) => EntityVariable::fromObject($entity), $entities));
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    /**
     * @param Entity[] $entities
     * @return Entity[]
     */
    abstract protected function filterEntities(array $entities): array;

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->aabb->createFormElement($variables),
            new WorldVariableDropdown($variables, $this->getWorldVariableName()),
            new ExampleInput("@action.form.resultVariableName", "entities", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->aabb->set($content[0]);
        $this->setWorldVariableName($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->aabb->get(), $this->getWorldVariableName(), $this->getResultName()];
    }
}
