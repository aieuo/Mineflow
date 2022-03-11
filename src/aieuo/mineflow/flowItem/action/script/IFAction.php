<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IFAction extends IFActionBase {

    protected string $name = "action.if.name";
    protected string $detail = "action.if.description";

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_IF, conditions: $conditions, actions: $actions, customName: $customName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
        return true;
    }
}