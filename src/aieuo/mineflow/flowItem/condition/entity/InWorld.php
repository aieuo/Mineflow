<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class InWorld extends SimpleCondition {

    private EntityArgument $entity;
    private StringArgument $world;

    public function __construct(string $entity = "", string $world = "") {
        parent::__construct(self::IN_WORLD, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("target", $entity),
            $this->world = new StringArgument("world", $world, "@action.createPosition.form.world", example: "world"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getWorld(): StringArgument {
        return $this->world;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $world = $this->world->getString($source);

        yield Await::ALL;
        return $entity->getPosition()->getWorld()->getFolderName() === $world;
    }
}
