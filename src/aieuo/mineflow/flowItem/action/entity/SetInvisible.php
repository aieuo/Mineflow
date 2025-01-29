<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class SetInvisible extends SimpleAction {

    public function __construct(string $entity = "", bool $invisible = true) {
        parent::__construct(self::SET_INVISIBLE, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            BooleanArgument::create("invisible", $invisible)
                ->format(fn(bool $value) => Language::get("action.setInvisible.".($value ? "visible" : "invisible"))),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getInvisible(): BooleanArgument {
        return $this->getArgument("invisible");
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getEntity($source);
        $entity->setInvisible($this->getInvisible()->getBool());
        yield Await::ALL;
    }
}