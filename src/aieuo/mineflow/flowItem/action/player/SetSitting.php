<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

class SetSitting extends FlowItem implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::SET_SITTING;

    protected $name = "action.setSitting.name";
    protected $detail = "action.setSitting.detail";
    protected $detailDefaultReplace = ["player", "position"];

    protected $category = Category::PLAYER;

    /** @var array */
    private static $entityIds = [];

    public function __construct(string $player = "", string $position = "") {
        $this->setPlayerVariableName($player);
        $this->setPositionVariableName($position);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPositionVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getPositionVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $position = $this->getPosition($source);

        self::leave($player);

        $pk = new AddActorPacket();
        $pk->entityRuntimeId = Entity::$entityCount++;
        $pk->type = "minecraft:minecart";
        $pk->position = $position;
        $pk->links = [new EntityLink($pk->entityRuntimeId, $player->getId(), EntityLink::TYPE_RIDER, false, true)];
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
        ];
        $player->dataPacket($pk);

        self::$entityIds[$player->getName()] = $pk->entityRuntimeId;
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPositionVariableName()];
    }

    public static function leave(Player $player): void {
        if (isset(self::$entityIds[$player->getName()])) {
            $pk = new RemoveActorPacket();
            $pk->entityUniqueId = self::$entityIds[$player->getName()];
            if ($player->isOnline()) $player->dataPacket($pk);
            unset(self::$entityIds[$player->getName()]);
        }
    }
}
