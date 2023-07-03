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

class SetPitch extends SimpleAction {

    public function __construct(string $entity = "", float $pitch = null) {
        parent::__construct(self::SET_PITCH, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new NumberArgument("pitch", $pitch, example: "180"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getPitch(): NumberArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pitch = $this->getPitch()->getFloat($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setRotation($entity->getLocation()->getYaw(), $pitch);
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $entity->getLocation()->getYaw(), $pitch);

        yield Await::ALL;
    }
}
