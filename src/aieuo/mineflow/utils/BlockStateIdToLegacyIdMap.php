<?php
declare(strict_types=1);


namespace aieuo\mineflow\utils;

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

class BlockStateIdToLegacyIdMap {

    /** @var array<int, array> */
    private array $map = [];

    public function __construct() {
        $upgrader = GlobalBlockStateHandlers::getUpgrader();
        $deserializer = GlobalBlockStateHandlers::getDeserializer();

        $legacyBlockIdToStringIdMap = LegacyBlockIdToStringIdMap::getInstance()->getLegacyToStringMap();
        foreach ($legacyBlockIdToStringIdMap as $legacyId => $stringId) {
            for ($meta = 0; $meta < 16; $meta ++) {
                try {
                    $stack = $upgrader->upgradeIntIdMeta($legacyId, $meta);
                    $stateId = $deserializer->deserialize($stack);
                } catch (BlockStateDeserializeException) {
                    break;
                }

                if (isset($this->map[$stateId])) break;

                $this->map[$stateId] = [$legacyId, $meta];
            }
        }
    }

    public function get(int $stateId): ?array {
        return $this->map[$stateId] ?? null;
    }

}