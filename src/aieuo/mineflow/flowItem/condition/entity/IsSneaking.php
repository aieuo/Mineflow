<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Human;
use SOFe\AwaitGenerator\Await;

class IsSneaking extends SimpleCondition {

    private EntityArgument $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_SNEAKING, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("target", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        yield Await::ALL;
        return $entity instanceof Human and $entity->isSneaking();
    }
}
