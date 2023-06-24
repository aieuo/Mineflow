<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class ClearAllEffect extends SimpleAction {

    private EntityPlaceholder $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::CLEAR_ALL_EFFECT, FlowItemCategory::ENTITY);

        $this->setPlaceholders([
            $this->entity = new EntityPlaceholder("entity", $entity),
        ]);
    }

    public function getEntity(): EntityPlaceholder {
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
