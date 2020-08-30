<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Creature;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\recipe\Recipe;

class IsCreature extends IsActiveEntity {

    protected $id = self::IS_CREATURE;

    protected $name = "condition.isCreature.name";
    protected $detail = "condition.isCreature.detail";

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        $entity = EntityHolder::findEntity((int)$id);
        return $entity instanceof Creature;
    }
}