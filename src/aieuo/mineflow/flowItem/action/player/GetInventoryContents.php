<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\InventoryObjectVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\Item;

class GetInventoryContents extends GetInventoryContentsBase {

    public function __construct(string $player = "", string $resultName = "inventory") {
        parent::__construct(self::GET_INVENTORY_CONTENTS, player: $player, resultName: $resultName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());

        $entity = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($entity);

        $variable = new InventoryObjectVariable($entity->getInventory());

        $source->addVariable($resultName, $variable);
        yield true;
        return $this->getResultName();
    }
}
