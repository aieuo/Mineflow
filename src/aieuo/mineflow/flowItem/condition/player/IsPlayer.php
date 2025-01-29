<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\CheckEntityStateById;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class IsPlayer extends CheckEntityStateById {

    public function __construct(string $entityId = "") {
        parent::__construct(self::IS_PLAYER, FlowItemCategory::PLAYER, $entityId);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->getEntityId()->getInt($source);

        yield Await::ALL;
        return EntityHolder::isPlayer($id);
    }
}