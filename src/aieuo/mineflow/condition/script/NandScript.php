<?php

namespace aieuo\mineflow\condition\script;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class NandScript extends AndScript {

    protected $id = self::SCRIPT_NAND;

    protected $name = "@script.nand.name";
    protected $description = "@script.nand.description";

    public function getDetail(): string {
        $details = ["-----------nand-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        return !$matched;
    }
}