<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class ORScript extends AndScript {

    protected $id = self::CONDITION_OR;

    protected $name = "condition.or.name";
    protected $description = "condition.or.description";

    public function getDetail(): string {
        $details = ["-----------or-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, Recipe $origin): bool {
        $matched = false;
        foreach ($this->getConditions() as $condition) {
            $result = $condition->execute($target, $origin);
            if ($result) $matched = true;
        }
        return $matched;
    }
}