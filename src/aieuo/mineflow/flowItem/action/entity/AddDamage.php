<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\event\entity\EntityDamageEvent;
use SOFe\AwaitGenerator\Await;

// TODO: event cause
class AddDamage extends SimpleAction {

    public function __construct(string $entity = "", string $damage = "") {
        parent::__construct(self::ADD_DAMAGE, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            NumberArgument::create("damage", $damage)->min(1)->example("10"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getDamage(): NumberArgument {
        return $this->getArgument("damage");
    }

    public function getCause(): int {
        return EntityDamageEvent::CAUSE_ENTITY_ATTACK;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->getDamage()->getFloat($source);
        $cause = $this->getCause();
        $entity = $this->getEntity()->getOnlineEntity($source);

        $event = new EntityDamageEvent($entity, $cause, $damage);
        $entity->attack($event);

        yield Await::ALL;
    }
}
