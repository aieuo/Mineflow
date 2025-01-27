<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Cancellable;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class EventCancel extends SimpleAction {

    public function __construct() {
        parent::__construct(self::EVENT_CANCEL, FlowItemCategory::EVENT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $event = $source->getEvent();
        if (!($event instanceof Cancellable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.eventCancel.notCancelable"));
        }
        $event->cancel();

        yield Await::ALL;
    }
}