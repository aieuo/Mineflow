<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IFNotAction extends IFAction {

    protected string $id = self::ACTION_IF_NOT;

    protected string $name = "action.ifnot.name";
    protected string $detail = "action.ifnot.description";

    public function getDetail(): string {
        $details = ["", "§7===========§f if not §7=============§f"];
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

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return false;
        }

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
        return true;
    }
}