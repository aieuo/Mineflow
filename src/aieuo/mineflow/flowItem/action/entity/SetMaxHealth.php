<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SetMaxHealth extends SetHealth {

    protected string $id = self::SET_MAX_HEALTH;

    protected string $name = "action.setMaxHealth.name";
    protected string $detail = "action.setMaxHealth.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setMaxHealth((int)$health);
        yield FlowItemExecutor::CONTINUE;
    }
}