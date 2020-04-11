<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class SetSitting extends Action implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected $id = self::SET_SITTING;

    protected $name = "action.setSitting.name";
    protected $detail = "action.setSitting.detail";
    protected $detailDefaultReplace = ["player", "position"];

    protected $category = Categories::CATEGORY_ACTION_PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var array */
    private static $entityIds = [];

    public function __construct(string $name = "target", string $position = "pos") {
        $this->playerVariableName = $name;
        $this->positionVariableName = $position;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPositionVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getPositionVariableName() !== "";
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        self::leave($player);

        $pk = new AddActorPacket();
        $pk->entityRuntimeId = ++Entity::$entityCount;
        $pk->type = 84;
        $pk->position = $position;
        $pk->links = [new EntityLink($pk->entityRuntimeId, $player->getId(), EntityLink::TYPE_RIDER)];
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
        ];
        $player->dataPacket($pk);

        self::$entityIds[$player->getName()] = $pk->entityRuntimeId;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@flowItem.form.target.position", Language::get("form.example", ["pos"]), $default[2] ?? $this->getPositionVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $data[2] = "pos";
        return ["status" => true, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPositionVariableName()];
    }

    public static function leave(Player $player) {
        if (isset(self::$entityIds[$player->getName()])) {
            $pk = new RemoveActorPacket();
            $pk->entityUniqueId = self::$entityIds[$player->getName()];
            if ($player->isOnline()) $player->dataPacket($pk);
            unset(self::$entityIds[$player->getName()]);
        }
    }
}
