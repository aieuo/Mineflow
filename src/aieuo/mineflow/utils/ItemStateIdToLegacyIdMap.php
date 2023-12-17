<?php
declare(strict_types=1);


namespace aieuo\mineflow\utils;

use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class ItemStateIdToLegacyIdMap {

    /** @var array<int, array> */
    private array $map = [];

    public function __construct() {
        $upgrader = GlobalItemDataHandlers::getUpgrader();
        $deserializer = GlobalItemDataHandlers::getDeserializer();

        $legacyItemIdToStringIdMap = LegacyItemIdToStringIdMap::getInstance()->getLegacyToStringMap();
        foreach ($legacyItemIdToStringIdMap as $legacyId => $stringId) {
            for ($meta = 0; $meta < 16; $meta ++) {
                try {
                    $stack = $upgrader->upgradeItemTypeDataString($stringId, $meta, 1, null);
                    $item = $deserializer->deserializeStack($stack);
                } catch (SavedDataLoadingException|ItemTypeDeserializeException) {
                    break;
                }

                $stateId = $item->getStateId();
                if (isset($this->map[$stateId])) break;

                $this->map[$stateId] = [$legacyId, $meta];
            }
        }
    }

    public function get(int $stateId): ?array {
        return $this->map[$stateId] ?? null;
    }

}