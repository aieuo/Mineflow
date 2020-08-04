<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class PlaySound extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::PLAY_SOUND;

    protected $name = "action.playSound.name";
    protected $detail = "action.playSound.detail";
    protected $detailDefaultReplace = ["player", "sound", "volume", "pitch"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $sound;
    /** @var string */
    private $volume;
    /** @var string */
    private $pitch;

    public function __construct(string $player = "target", string $sound = "", string $volume = "1", string $pitch = "1") {
        $this->setPlayerVariableName($player);
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
        return $this->getPlayerVariableName() !== "" and $this->sound !== "" and $this->volume !== "" and $this->pitch !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $sound = $origin->replaceVariables($this->getSound());
        $volume = $origin->replaceVariables($this->getVolume());
        $pitch = $origin->replaceVariables($this->getPitch());

        $this->throwIfInvalidNumber($volume);
        $this->throwIfInvalidNumber($pitch);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $player->x;
        $pk->y = $player->y;
        $pk->z = $player->z;
        $pk->volume = (float)$volume;
        $pk->pitch = (float)$pitch;
        $player->dataPacket($pk);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.playSound.form.sound", Language::get("form.example", ["random.levelup"]), $default[2] ?? $this->getSound()),
                new Input("@action.playSound.form.volume", Language::get("form.example", ["1"]), $default[3] ?? $this->getVolume()),
                new Input("@action.playSound.form.pitch", Language::get("form.example", ["1"]), $default[4] ?? $this->getPitch()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") {
            $errors[] = ["@form.insufficient", 3];
        } elseif (!Main::getVariableHelper()->containsVariable($data[3]) and !is_numeric($data[3])) {
            $errors[] = ["@flowItem.error.notNumber", 3];
        }
        if ($data[4] === "") {
            $errors[] = ["@form.insufficient", 4];
        } elseif (!Main::getVariableHelper()->containsVariable($data[4]) and !is_numeric($data[4])) {
            $errors[] = ["@flowItem.error.notNumber", 4];
        }
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
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