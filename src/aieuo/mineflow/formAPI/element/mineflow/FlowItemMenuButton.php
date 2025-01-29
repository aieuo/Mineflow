<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use pocketmine\player\Player;

class FlowItemMenuButton extends Button {

    public function __construct(
        string $text,
        FlowItem $item,
        FlowItemContainer $parent,
        string $itemType,
        callable $onClick,
        ?ButtonImage $image = null
    ) {
        parent::__construct($text, function (Player $player) use($onClick, $item, $parent, $itemType) {
            $onClick($player, $parent, $itemType, $item);
        }, $image);
    }
}