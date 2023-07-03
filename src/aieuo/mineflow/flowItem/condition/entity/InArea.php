<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class InArea extends SimpleCondition {

    public function __construct(string $entity = "", string $pos1 = "", string $pos2 = "") {
        parent::__construct(self::IN_AREA, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("target", $entity),
            new PositionArgument("pos1", $pos1, "@condition.inArea.form.pos1"),
            new PositionArgument("pos2", $pos2, "@condition.inArea.form.pos2"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getPosition1(): PositionArgument {
        return $this->getArguments()[1];
    }

    public function getPosition2(): PositionArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);
        $pos1 = $this->getPosition1()->getPosition($source);
        $pos2 = $this->getPosition2()->getPosition($source);
        $pos = $entity->getLocation()->floor();

        yield Await::ALL;
        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }
}
