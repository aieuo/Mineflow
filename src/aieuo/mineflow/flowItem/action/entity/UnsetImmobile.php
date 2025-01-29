<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class UnsetImmobile extends SimpleAction {

    public function __construct(string $entity = "") {
        parent::__construct(self::UNSET_IMMOBILE, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setNoClientPredictions(false);

        yield Await::ALL;
    }
}