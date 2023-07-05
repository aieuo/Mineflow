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
use SOFe\AwaitGenerator\Await;

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
            new EntityArgument("entity", $entity),
            new ItemArgument("item", $item),
            new IntEnumArgument("index", $index, $this->slots),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getItem(): ItemArgument {
        return $this->getArguments()[1];
    }

    public function getIndex(): IntEnumArgument {
        return $this->getArguments()[2];
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
