<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class InWorld extends SimpleCondition {

    public function __construct(string $entity = "", string $world = "") {
        parent::__construct(self::IN_WORLD, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("target", $entity),
            StringArgument::create("world", $world, "@action.createPosition.form.world")->example("world"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("target");
    }

    public function getWorld(): StringArgument {
        return $this->getArgument("world");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);
        $world = $this->getWorld()->getString($source);

        yield Await::ALL;
        return $entity->getPosition()->getWorld()->getFolderName() === $world;
    }
}