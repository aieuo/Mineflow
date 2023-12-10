<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class IFAction extends IFActionBase {

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_IF, conditions: $conditions, actions: $actions, customName: $customName);
    }

    public function getName(): string {
        return Language::get("action.if.name");
    }


    public function getDescription(): string {
        return Language::get("action.if.description");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }

        yield from (new FlowItemExecutor($this->getActions()->getItems(), $source->getTarget(), [], $source))->getGenerator();
        yield Await::ALL;

        return true;
    }
}
