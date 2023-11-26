<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetYaw extends SimpleAction {

    public function __construct(string $entity = "", float $yaw = null) {
        parent::__construct(self::SET_YAW, FlowItemCategory::ENTITY);


        $this->setArguments([
            EntityArgument::create("entity", $entity),
            NumberArgument::create("yaw", $yaw)->example("180"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getYaw(): NumberArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $yaw = $this->getYaw()->getFloat($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setRotation($yaw, $entity->getLocation()->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $yaw, $entity->getLocation()->getPitch());

        yield Await::ALL;
    }
}
