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

    private EntityArgument $entity;
    private NumberArgument $yaw;

    public function __construct(string $entity = "", float $yaw = null) {
        parent::__construct(self::SET_YAW, FlowItemCategory::ENTITY);


        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->yaw = new NumberArgument("yaw", $yaw, example: "180"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getYaw(): NumberArgument {
        return $this->yaw;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $yaw = $this->yaw->getFloat($source);
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setRotation($yaw, $entity->getLocation()->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $yaw, $entity->getLocation()->getPitch());

        yield Await::ALL;
    }
}
