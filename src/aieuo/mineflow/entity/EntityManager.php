<?php
declare(strict_types=1);

namespace aieuo\mineflow\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class EntityManager {
    public static function init(): void {
        EntityFactory::getInstance()->register(MineflowHuman::class, function (World $world, CompoundTag $nbt): MineflowHuman {
            return new MineflowHuman(EntityDataHelper::parseLocation($nbt, $world), MineflowHuman::parseSkinNBT($nbt), $nbt);
        }, ["MineflowHuman"]);
    }
}