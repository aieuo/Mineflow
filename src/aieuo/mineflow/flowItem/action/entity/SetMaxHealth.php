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

        $health = $this->getInt($source->replaceVariables($this->getHealth()), min: 1);
        $entity = $this->getOnlineEntity($source);

        $entity->setMaxHealth($health);
        yield FlowItemExecutor::CONTINUE;
    }
}