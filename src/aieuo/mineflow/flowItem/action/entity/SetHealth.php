<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SetHealth extends SetHealthBase {

    protected string $name = "action.setHealth.name";
    protected string $detail = "action.setHealth.detail";

    public function __construct(string $entity = "", string $health = "") {
        parent::__construct(self::SET_HEALTH, entity: $entity, health: $health);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setHealth((float)$health);
        yield true;
    }
}