<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetSitting extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private static array $entityIds = [];

    private PlayerPlaceholder $player;
    private PositionPlaceholder $position;

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SITTING, FlowItemCategory::PLAYER);

        $this->player = new PlayerPlaceholder("player", $player);
        $this->position = new PositionPlaceholder("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->position->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->position->get()];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->position->isNotEmpty();
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $position = $this->position->getPosition($source);

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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->position->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->position->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->position->get()];
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
