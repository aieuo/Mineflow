<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class IFNotAction extends IFActionBase {

    protected string $name = "action.if_not.name";
    protected string $detail = "action.if_not.description";

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_IF_NOT, conditions: $conditions, actions: $actions, customName: $customName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return false;
        }

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
        return true;
    }
}