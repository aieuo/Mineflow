<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\recipe\Recipe;

class ElseifAction extends IFAction {

    protected $id = self::ACTION_ELSEIF;

    protected $name = "action.elseif.name";
    protected $detail = "action.elseif.description";

    public function getDetail(): string {
        $details = ["§7=============§f elseif §7=============§f"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "§7~~~~~~~~~~~~~~~~~~~~~~~~~~~§f";
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function execute(Recipe $source): \Generator {
        $lastResult = $this->getParent()->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException();
        if ($lastResult) return true;

        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }

        yield from $this->executeAll($source, FlowItemContainer::ACTION);
        return true;
    }
}