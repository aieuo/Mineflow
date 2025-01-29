<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class ElseifAction extends IFActionBase {

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_ELSEIF, conditions: $conditions, actions: $actions, customName: $customName);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $lastResult = $source->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException($this->getName());
        if ($lastResult) return true;

        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }

        yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [], $source))->getGenerator();
        return true;
    }
}