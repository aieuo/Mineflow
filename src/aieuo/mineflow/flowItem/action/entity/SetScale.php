<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class SetScale extends SimpleAction {

    public function __construct(string $entity = "", float $scale = null) {
        parent::__construct(self::SET_SCALE, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            NumberArgument::create("scale", $scale)->min(0)->excludes([0])->example("1"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getScale(): NumberArgument {
        return $this->getArgument("scale");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $scale = $this->getScale()->getFloat($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setScale($scale);

        yield Await::ALL;
    }
}