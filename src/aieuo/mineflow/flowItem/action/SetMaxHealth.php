<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SetMaxHealth extends SetHealth {

    protected $id = self::SET_MAX_HEALTH;

    protected $name = "action.setMaxHealth.name";
    protected $detail = "action.setMaxHealth.detail";

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $health = $origin->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setMaxHealth((int)$health);
        yield true;
        return true;
    }
}