<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\object\InventoryVariable;
use SOFe\AwaitGenerator\Await;

class GetArmorInventoryContents extends GetInventoryContentsBase {

    public function __construct(string $player = "", string $resultName = "inventory") {
        parent::__construct(self::GET_ARMOR_INVENTORY_CONTENTS, player: $player, resultName: $resultName);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);
        $entity = $this->getPlayer()->getOnlinePlayer($source);
        $variable = new InventoryVariable($entity->getArmorInventory());

        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }
}
