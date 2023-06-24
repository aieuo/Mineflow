<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetHealth extends SetHealthBase {

    public function __construct(string $entity = "", string $health = "") {
        parent::__construct(self::SET_HEALTH, entity: $entity, health: $health);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = $this->getFloat($source->replaceVariables($this->getHealth()), min: 0);
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setHealth($health);

        yield Await::ALL;
    }
}
