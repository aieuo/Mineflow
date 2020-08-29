<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;

class PlaySoundAt extends Action implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::PLAY_SOUND_AT;

    protected $name = "action.playSoundAt.name";
    protected $detail = "action.playSoundAt.detail";
    protected $detailDefaultReplace = ["position", "sound", "volume", "pitch"];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $sound;
    /** @var string */
    private $volume;
    /** @var string */
    private $pitch;

    public function __construct(string $position = "pos", string $sound = "", string $volume = "1", string $pitch = "1") {
        $this->setPositionVariableName($position);
        $this->sound = $sound;
        $this->volume = $volume;
        $this->pitch = $pitch;
    }

    public function setSound(string $health) {
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
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $sound = $origin->replaceVariables($this->getSound());
        $volume = $origin->replaceVariables($this->getVolume());
        $pitch = $origin->replaceVariables($this->getPitch());

        $this->throwIfInvalidNumber($volume);
        $this->throwIfInvalidNumber($pitch);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $position->x;
        $pk->y = $position->y;
        $pk->z = $position->z;
        $pk->volume = (float)$volume;
        $pk->pitch = (float)$pitch;
        Server::getInstance()->broadcastPacket($position->level->getPlayers(), $pk);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.position", "pos", $default[1] ?? $this->getPositionVariableName()), true,
                new ExampleInput("@action.playSound.form.sound", "random.levelup", $default[2] ?? $this->getSound(), true),
                new ExampleNumberInput("@action.playSound.form.volume", "1", $default[3] ?? $this->getVolume(), true),
                new ExampleNumberInput("@action.playSound.form.pitch", "1", $default[4] ?? $this->getPitch(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
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