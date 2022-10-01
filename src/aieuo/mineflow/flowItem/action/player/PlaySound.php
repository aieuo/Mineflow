<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class PlaySound extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "action.playSound.name";
    protected string $detail = "action.playSound.detail";
    protected array $detailDefaultReplace = ["player", "sound", "volume", "pitch"];

    public function __construct(
        string         $player = "",
        private string $sound = "",
        private string $volume = "1",
        private string $pitch = "1"
    ) {
        parent::__construct(self::PLAY_SOUND, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function setSound(string $health): void {
        $this->sound = $health;
    }

    public function getSound(): string {
        return $this->sound;
    }

    public function setVolume(string $volume): void {
        $this->volume = $volume;
    }

    public function getVolume(): string {
        return $this->volume;
    }

    public function setPitch(string $pitch): void {
        $this->pitch = $pitch;
    }

    public function getPitch(): string {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->sound !== "" and $this->volume !== "" and $this->pitch !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $sound = $source->replaceVariables($this->getSound());
        $volume = $source->replaceVariables($this->getVolume());
        $pitch = $source->replaceVariables($this->getPitch());

        $this->throwIfInvalidNumber($volume);
        $this->throwIfInvalidNumber($pitch);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $player->getLocation()->getX();
        $pk->y = $player->getLocation()->getY();
        $pk->z = $player->getLocation()->getZ();
        $pk->volume = (float)$volume;
        $pk->pitch = (float)$pitch;
        $player->getNetworkSession()->sendDataPacket($pk);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.playSound.form.sound", "random.levelup", $this->getSound(), true),
            new ExampleNumberInput("@action.playSound.form.volume", "1", $this->getVolume(), true),
            new ExampleNumberInput("@action.playSound.form.pitch", "1", $this->getPitch(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setSound($content[1]);
        $this->setVolume($content[2]);
        $this->setPitch($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()];
    }
}