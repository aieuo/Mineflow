<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetInvisible extends SimpleAction {

    private EntityArgument $entity;
    private BooleanArgument $invisible;

    public function __construct(string $entity = "", bool $invisible = true) {
        parent::__construct(self::SET_INVISIBLE, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->invisible = new BooleanArgument(
                "invisible", $invisible,
                toStringFormatter: fn(bool $value) => Language::get("action.setInvisible.".($value ? "visible" : "invisible"))
            ),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getInvisible(): BooleanArgument {
        return $this->invisible;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getEntity($source);
        $entity->setInvisible($this->invisible->getBool());
        yield Await::ALL;
    }
}
