<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SetMaxHealth extends SetHealth {

    protected $id = self::SET_MAX_HEALTH;

    protected $name = "action.setMaxHealth.name";
    protected $detail = "action.setMaxHealth.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setMaxHealth((int)$health);
        yield true;
    }
}