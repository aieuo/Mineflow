<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerBucketFillEvent;

class PlayerBucketFillEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerBucketFillEvent", PlayerBucketFillEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerBucketFillEvent $event */
        $target = $event->getPlayer();
        $blockClicked = $event->getBlockClicked();
        $blockFace = $event->getBlockFace();
        $bucket = $event->getBucket();
        $item = $event->getItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($blockClicked, "blockClicked"), [
            "blockFace" => new NumberVariable($blockFace),
            "bucket" => new ItemVariable($bucket),
            "item" => new ItemVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "blockClicked" => new DummyVariable(BlockVariable::class),
            "blockFace" => new DummyVariable(NumberVariable::class),
            "bucket" => new DummyVariable(ItemVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
        ];
    }
}