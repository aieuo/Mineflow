<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SetMaxHealth extends SetHealthBase {

    protected string $name = "action.setMaxHealth.name";
    protected string $detail = "action.setMaxHealth.detail";

    public function __construct(string $entity = "", string $health = "") {
        parent::__construct(self::SET_MAX_HEALTH, entity: $entity, health: $health);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setMaxHealth((int)$health);
        yield true;
    }
}