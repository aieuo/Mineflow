<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class SetMaxHealth extends SetHealthBase {

    public function __construct(string $entity = "", int $health = null) {
        parent::__construct(self::SET_MAX_HEALTH, entity: $entity, health: $health);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = Utils::getInt($this->getHealth()->getInt($source), min: 1);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setMaxHealth($health);

        yield Await::ALL;
    }
}