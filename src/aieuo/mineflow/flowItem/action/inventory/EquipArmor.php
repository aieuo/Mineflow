<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Living;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class EquipArmor extends SimpleAction {

    private array $slots = [
        "action.equipArmor.helmet",
        "action.equipArmor.chestplate",
        "action.equipArmor.leggings",
        "action.equipArmor.boots",
    ];

    public function __construct(string $entity = "", string $item = "", int $index = 0) {
        parent::__construct(self::EQUIP_ARMOR, FlowItemCategory::INVENTORY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            ItemArgument::create("item", $item),
            IntEnumArgument::create("index", $index)->options($this->slots),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getIndex(): IntEnumArgument {
        return $this->getArgument("index");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getIndex()->getEnumValue();
        $entity = $this->getEntity()->getOnlineEntity($source);
        $item = $this->getItem()->getItem($source);

        if ($entity instanceof Living) {
            $entity->getArmorInventory()->setItem($index, $item);
        }

        yield Await::ALL;
    }
}