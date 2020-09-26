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
        $details = ["=============elseif============="];
        foreach ($this->getItems(FlowItemContainer::CONDITION) as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->getItems(FlowItemContainer::ACTION) as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(Recipe $origin) {
        $lastResult = $this->getParent()->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException();
        if ($lastResult) return true;

        foreach ($this->getItems(FlowItemContainer::CONDITION) as $condition) {
            if (!(yield from $condition->execute($origin))) return false;
        }

        yield from $this->executeAll($origin, FlowItemContainer::ACTION);
        return true;
    }
}