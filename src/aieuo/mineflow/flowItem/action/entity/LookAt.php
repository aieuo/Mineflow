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

    private PositionArgument $position;
    private EntityArgument $entity;

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::LOOK_AT, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->position = new PositionArgument("position", $position),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $position = $this->position->getPosition($source);

        if ($entity instanceof Living) {
            $entity->lookAt($position);
        }

        yield Await::ALL;
    }
}
