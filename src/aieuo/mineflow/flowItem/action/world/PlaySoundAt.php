<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;

class PlaySoundAt extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected string $id = self::PLAY_SOUND_AT;

    protected string $name = "action.playSoundAt.name";
    protected string $detail = "action.playSoundAt.detail";
    protected array $detailDefaultReplace = ["position", "sound", "volume", "pitch"];

    protected string $category = Category::WORLD;

    private string $sound;
    private string $volume;
    private string $pitch;

    public function __construct(string $position = "", string $sound = "", string $volume = "1", string $pitch = "1") {
        $this->setPositionVariableName($position);
        $this->sound = $sound;
        $this->volume = $volume;
        $this->pitch = $pitch;
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
        return $this->getPositionVariableName() !== "" and $this->sound !== "" and $this->volume !== "" and $this->pitch !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $sound = $source->replaceVariables($this->getSound());
        $volume = $this->getFloat($source->replaceVariables($this->getVolume()));
        $pitch = $this->getFloat($source->replaceVariables($this->getPitch()));
        $position = $this->getPosition($source);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $position->x;
        $pk->y = $position->y;
        $pk->z = $position->z;
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        Server::getInstance()->broadcastPacket($position->level->getPlayers(), $pk);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleInput("@action.playSound.form.sound", "random.levelup", $this->getSound(), true),
            new ExampleNumberInput("@action.playSound.form.volume", "1", $this->getVolume(), true),
            new ExampleNumberInput("@action.playSound.form.pitch", "1", $this->getPitch(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setSound($content[1]);
        $this->setVolume($content[2]);
        $this->setPitch($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()];
    }
}