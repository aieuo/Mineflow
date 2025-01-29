<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class IsCreatureVariable extends CheckEntityState {

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_CREATURE_VARIABLE, entity: $entity);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        yield Await::ALL;
        return $entity instanceof Living;
    }
}