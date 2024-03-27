<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Human;
use SOFe\AwaitGenerator\Await;

class IsSneaking extends SimpleCondition {

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_SNEAKING, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("target", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("target");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        yield Await::ALL;
        return $entity instanceof Human and $entity->isSneaking();
    }
}
