<?php

namespace aieuo\mineflow\utils;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;

class Bossbar {
    /** @var Bossbar[][] */
    public static array $bars = [];

    private int $entityId;

    public function __construct(
        private string $title,
        private float $max = 1.0,
        private float $per = 1.0,
        private int $color = BossBarColor::PURPLE,
    ) {
        $this->entityId = Entity::nextRuntimeId();
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setMax(float $max): void {
        $this->max = $max;
    }

    public function getMax(): float {
        return $this->max;
    }

    public function setPercentage(float $per): void {
        if ($per > $this->max) $per = $this->max;
        $this->per = $per;
    }

    public function getPercentage(): float {
        return $this->per;
    }

    public function getEntityId(): int {
        return $this->entityId;
    }

    public function getColor(): int {
        return $this->color;
    }

    public function setColor(int $color): void {
        $this->color = $color;
    }

    public static function add(Player $player, string $id, string $title, float $max, float $per, int $color): void {
        if (isset(self::$bars[$player->getName()][$id])) self::remove($player, $id);
        $bar = new Bossbar($title, $max, $per, $color);
        self::$bars[$player->getName()][$id] = $bar;

        $pk = AddActorPacket::create(
            $bar->getEntityId(),
            $bar->getEntityId(),
            EntityIds::SHULKER,
            Vector3::zero(),
            null,
            0,
            0,
            0,
            0,
            [],
            [
                EntityMetadataProperties::FLAGS => new LongMetadataProperty(
                    (1 << EntityMetadataFlags::INVISIBLE) | (1 << EntityMetadataFlags::IMMOBILE)
                ),
                EntityMetadataProperties::NAMETAG => new StringMetadataProperty($title)
            ],
            new PropertySyncData([], []),
            []
        );
        $player->getNetworkSession()->sendDataPacket($pk);

        $pk2 = BossEventPacket::show($bar->getEntityId(), $title, $per, color: $bar->getColor());
        $player->getNetworkSession()->sendDataPacket($pk2);
    }

    public static function remove(Player $player, string $id): bool {
        if (!isset(self::$bars[$player->getName()][$id])) return false;
        $bar = self::$bars[$player->getName()][$id];
        $pk = BossEventPacket::hide($bar->getEntityId());
        $player->getNetworkSession()->sendDataPacket($pk);

        $pk2 = RemoveActorPacket::create($bar->getEntityId());
        $player->getNetworkSession()->sendDataPacket($pk2);

        unset(self::$bars[$player->getName()][$id]);
        return true;
    }
}