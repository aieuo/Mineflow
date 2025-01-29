<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\object\InventoryVariable;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class GetInventoryContents extends GetInventoryContentsBase {

    public function __construct(string $player = "", string $resultName = "inventory") {
        parent::__construct(self::GET_INVENTORY_CONTENTS, player: $player, resultName: $resultName);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);
        $entity = $this->getPlayer()->getOnlinePlayer($source);

        $variable = new InventoryVariable($entity->getInventory());

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }
}