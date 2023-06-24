<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class ClearAllEffect extends SimpleAction {

    private EntityArgument $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::CLEAR_ALL_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        if ($entity instanceof Living) {
            $entity->getEffects()->clear();
        }

        yield Await::ALL;
    }
}
