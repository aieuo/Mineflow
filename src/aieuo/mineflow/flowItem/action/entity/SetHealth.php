<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class SetHealth extends SetHealthBase {

    public function __construct(string $entity = "", int $health = null) {
        parent::__construct(self::SET_HEALTH, entity: $entity, health: $health);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = $this->getHealth()->getFloat($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setHealth($health);

        yield Await::ALL;
    }
}