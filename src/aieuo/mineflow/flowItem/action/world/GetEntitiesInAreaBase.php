<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\AxisAlignedBBArgument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use SOFe\AwaitGenerator\Await;
use function array_map;

abstract class GetEntitiesInAreaBase extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private AxisAlignedBBArgument $aabb;
    private WorldArgument $world;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::WORLD,
        string         $aabb = "",
        string         $worldName = "",
        private string $resultName = "entities"
    ) {
        parent::__construct($id, $category);

        $this->aabb = new AxisAlignedBBArgument("aabb", $aabb);
        $this->world = new WorldArgument("world", $worldName);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->aabb->getName(), $this->world->getName(), "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->aabb->get(), $this->world->get(), $this->getResultName()];
    }

    public function getAxisAlignedBB(): AxisAlignedBBArgument {
        return $this->aabb;
    }

    public function getWorld(): WorldArgument {
        return $this->world;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function isDataValid(): bool {
        return $this->aabb->isNotEmpty() and $this->world->isNotEmpty() and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $aabb = $this->aabb->getAxisAlignedBB($source);
        $world = $this->world->getWorld($source);
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
            $this->world->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "entities", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->aabb->set($content[0]);
        $this->world->set($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->aabb->get(), $this->world->get(), $this->getResultName()];
    }
}
