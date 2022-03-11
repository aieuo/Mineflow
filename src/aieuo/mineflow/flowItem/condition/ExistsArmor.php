<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExistsArmor extends TypeItem {

    protected string $name = "condition.existsArmor.name";
    protected string $detail = "condition.existsArmor.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::EXISTS_ARMOR, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getArmorInventory()->contains($item);
    }
}
