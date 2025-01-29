<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\CheckEntityState;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class IsPlayerVariable extends CheckEntityState {

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_PLAYER_VARIABLE, FlowItemCategory::PLAYER, $entity);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        yield Await::ALL;
        return $entity instanceof Player;
    }
}