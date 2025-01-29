<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class ClearAllEffect extends SimpleAction {

    public function __construct(string $entity = "") {
        parent::__construct(self::CLEAR_ALL_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        if ($entity instanceof Living) {
            $entity->getEffects()->clear();
        }

        yield Await::ALL;
    }
}