<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\Item;

class GetArmorInventoryContents extends GetInventoryContentsBase {

    protected string $name = "action.getArmorInventory.name";
    protected string $detail = "action.getArmorInventory.detail";

    public function __construct(string $player = "", string $resultName = "inventory") {
        parent::__construct(self::GET_ARMOR_INVENTORY_CONTENTS, player: $player, resultName: $resultName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());

        $entity = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($entity);

        $variable = new ListVariable(array_map(fn(Item $item) => new ItemVariable($item), $entity->getArmorInventory()->getContents(true)));

        $source->addVariable($resultName, $variable);
        yield true;
        return $this->getResultName();
    }
}
