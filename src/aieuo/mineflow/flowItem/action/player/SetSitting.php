<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetSitting extends SimpleAction {

    private static array $entityIds = [];

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SITTING, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            PositionArgument::create("position", $position),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $position = $this->getPosition()->getPosition($source);

        self::leave($player);

        $pk = new AddActorPacket();
        $pk->actorUniqueId = Entity::nextRuntimeId();
        $pk->actorRuntimeId = $pk->actorUniqueId;
        $pk->type = EntityIds::MINECART;
        $pk->position = $position;
        $pk->links = [new EntityLink($pk->actorRuntimeId, $player->getId(), EntityLink::TYPE_RIDER, false, true)];
        $pk->metadata = [
            new LongMetadataProperty(EntityMetadataFlags::INVISIBLE),
        ];
        $player->getNetworkSession()->sendDataPacket($pk);

        self::$entityIds[$player->getName()] = $pk->actorRuntimeId;

        yield Await::ALL;
    }

    public static function leave(Player $player): void {
        if (isset(self::$entityIds[$player->getName()])) {
            $pk = new RemoveActorPacket();
            $pk->actorUniqueId = self::$entityIds[$player->getName()];
            if ($player->isOnline()) $player->getNetworkSession()->sendDataPacket($pk);
            unset(self::$entityIds[$player->getName()]);
        }
    }
}
