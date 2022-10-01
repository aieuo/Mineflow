<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;

class ElseifAction extends IFAction {

    protected string $id = self::ACTION_ELSEIF;

    protected string $name = "action.elseif.name";
    protected string $detail = "action.elseif.description";

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

    public function execute(FlowItemExecutor $source): \Generator {
        $lastResult = $source->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException($this->getName());
        if ($lastResult) return true;

        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
        return true;
    }
}