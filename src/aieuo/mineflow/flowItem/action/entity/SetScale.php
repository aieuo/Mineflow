<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetScale extends SimpleAction {

    public function __construct(string $entity = "", float $scale = null) {
        parent::__construct(self::SET_SCALE, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new NumberArgument("scale", $scale, example: "1", min: 0, excludes: [0]),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getScale(): NumberArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $scale = $this->getScale()->getFloat($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setScale($scale);

        yield Await::ALL;
    }
}
