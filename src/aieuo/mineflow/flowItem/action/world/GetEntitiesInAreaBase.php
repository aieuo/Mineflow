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
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;
use function array_map;

abstract class GetEntitiesInAreaBase extends SimpleAction {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::WORLD,
        string $aabb = "",
        string $worldName = "",
        string $resultName = "entities"
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            AxisAlignedBBArgument::create("aabb", $aabb),
            WorldArgument::create("world", $worldName),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("entities"),
        ]);
    }

    public function getAxisAlignedBB(): AxisAlignedBBArgument {
        return $this->getArgument("aabb");
    }

    public function getWorld(): WorldArgument {
        return $this->getArgument("world");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $aabb = $this->getAxisAlignedBB()->getAxisAlignedBB($source);
        $world = $this->getWorld()->getWorld($source);
        $result = $this->getResultName()->getString($source);

        $entities = $this->filterEntities($world->getNearbyEntities($aabb));
        $variable = new ListVariable(array_map(fn(Entity $entity) => EntityVariable::fromObject($entity), $entities));
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    /**
     * @param Entity[] $entities
     * @return Entity[]
     */
    abstract protected function filterEntities(array $entities): array;
}