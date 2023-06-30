<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\event\CustomTriggerCallEvent;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use SOFe\AwaitGenerator\Await;

class CallCustomTrigger extends SimpleAction {

    private StringArgument $triggerName;

    public function __construct(string $triggerName = "") {
        parent::__construct(self::CALL_CUSTOM_TRIGGER, FlowItemCategory::EVENT);

        $this->setArguments([
            $this->triggerName = new StringArgument("identifier", $triggerName, example: "aieuo"),
        ]);
    }

    public function getTriggerName(): StringArgument {
        return $this->triggerName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->triggerName->getString($source);
        $trigger = new CustomTrigger($name);

        TriggerHolder::executeRecipeAll($trigger, $source->getTarget(), [], $source->getEvent());

        (new CustomTriggerCallEvent($trigger, $source))->call();
        yield Await::ALL;
    }
}
