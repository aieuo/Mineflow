<?php

namespace aieuo\mineflow\action\script;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class ElseifScript extends IFScript {

    protected $id = self::SCRIPT_ELSEIF;

    protected $name = "@script.elseif.name";
    protected $description = "@script.elseif.description";

    public function getDetail(): string {
        $details = ["=============elseif============="];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($origin instanceof Recipe)) return null;

        $lastResult = $origin->getLastActionResult();
        if ($lastResult === null) return null;
        if ($lastResult) return false;

        $matched = true;
        foreach ($this->getConditions() as $condition) {
            $result = $condition->execute($target, $origin);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        if (!$matched) return false;

        foreach ($this->getActions() as $action) {
            $result = $action->execute($target, $origin);
            if ($result === null) return null;
        }
        return true;
    }
}