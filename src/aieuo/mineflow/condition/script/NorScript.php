<?php

namespace aieuo\mineflow\condition\script;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class NorScript extends AndScript {

    protected $id = self::SCRIPT_NOR;

    protected $name = "@script.nor.name";
    protected $description = "@script.nor.description";

    public function getDetail(): string {
        $details = ["-----------nor-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $matched = false;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);
            if ($result === null) return null;
            if ($result) $matched = true;
        }
        return !$matched;
    }
}