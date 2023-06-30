<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class UnsetImmobile extends SimpleAction {

    private EntityArgument $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::UNSET_IMMOBILE, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setNoClientPredictions(false);

        yield Await::ALL;
    }
}
