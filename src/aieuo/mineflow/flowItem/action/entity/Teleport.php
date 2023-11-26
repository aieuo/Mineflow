<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class Teleport extends SimpleAction {

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::TELEPORT, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            PositionArgument::create("position", $position),
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

        $entity->teleport($position);

        yield Await::ALL;
    }
}
