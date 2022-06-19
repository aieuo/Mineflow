<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExistsArmor extends TypeItem {

    protected string $id = self::EXISTS_ARMOR;

    protected string $name = "condition.existsArmor.name";
    protected string $detail = "condition.existsArmor.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getArmorInventory()->contains($item);
    }
}