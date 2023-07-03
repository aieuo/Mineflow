<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class LookAt extends SimpleAction {

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::LOOK_AT, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new PositionArgument("position", $position),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);
        $position = $this->getPosition()->getPosition($source);

        if ($entity instanceof Living) {
            $entity->lookAt($position);
        }

        yield Await::ALL;
    }
}
