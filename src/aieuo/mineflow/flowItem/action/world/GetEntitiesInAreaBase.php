<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\AxisAlignedBBArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\WorldArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use SOFe\AwaitGenerator\Await;
use function array_map;

abstract class GetEntitiesInAreaBase extends SimpleAction {

    private AxisAlignedBBArgument $aabb;
    private WorldArgument $world;
    private StringArgument $resultName;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::WORLD,
        string $aabb = "",
        string $worldName = "",
        string $resultName = "entities"
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->aabb = new AxisAlignedBBArgument("aabb", $aabb),
            $this->world = new WorldArgument("world", $worldName),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "entities"),
        ]);
    }

    public function getAxisAlignedBB(): AxisAlignedBBArgument {
        return $this->aabb;
    }

    public function getWorld(): WorldArgument {
        return $this->world;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $aabb = $this->aabb->getAxisAlignedBB($source);
        $world = $this->world->getWorld($source);
        $result = $this->resultName->getString($source);

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
}
