<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\EntityHolder;
use pocketmine\entity\Creature;

class IsCreature extends IsActiveEntity {

    protected $id = self::IS_CREATURE;

    protected $name = "condition.isCreature.name";
    protected $detail = "condition.isCreature.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getEntityId());
        $this->throwIfInvalidNumber($id);

        $entity = EntityHolder::findEntity((int)$id);

        yield true;
        return $entity instanceof Creature;
    }
}