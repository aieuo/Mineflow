<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use SOFe\AwaitGenerator\Await;

class SetItemDamage extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private NumberArgument $damage;

    public function __construct(string $item = "", int $damage = null) {
        parent::__construct(self::SET_ITEM_DAMAGE, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->damage = new NumberArgument("damage", $damage, example: "0", min: 0),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getDamage(): NumberArgument {
        return $this->damage;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->damage->getInt($source);
        $item = $this->item->getItem($source);

        $itemType = GlobalItemDataHandlers::getSerializer()->serializeType($item);
        $itemStack = GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataString($itemType->getName(), $damage, $item->getCount(), $item->getNamedTag());
        $newItem = GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStack);
        $this->item->getItemVariable($source)->setItem($newItem);

        yield Await::ALL;
        return $this->item->get();
    }
}
