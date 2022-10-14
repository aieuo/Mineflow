<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\object\InventoryObjectVariable;

class GetArmorInventoryContents extends GetInventoryContents {
    use PlayerFlowItemTrait;

    protected string $id = self::GET_ARMOR_INVENTORY_CONTENTS;

    protected string $name = "action.getArmorInventory.name";
    protected string $detail = "action.getArmorInventory.detail";


    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());

        $entity = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($entity);
        $variable = new InventoryObjectVariable($entity->getArmorInventory());

        $source->addVariable($resultName, $variable);
        yield true;
        return $this->getResultName();
    }
}
