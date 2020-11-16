<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class PlaySound extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::PLAY_SOUND;

    protected $name = "action.playSound.name";
    protected $detail = "action.playSound.detail";
    protected $detailDefaultReplace = ["player", "sound", "volume", "pitch"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $sound;
    /** @var string */
    private $volume;
    /** @var string */
    private $pitch;

    public function __construct(string $player = "", string $sound = "", string $volume = "1", string $pitch = "1") {
        $this->setPlayerVariableName($player);
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
        return $this->getPlayerVariableName() !== "" and $this->sound !== "" and $this->volume !== "" and $this->pitch !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getSound(), $this->getVolume(), $this->getPitch()]);
    }

    public function execute(Recipe $origin) {
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
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ExampleInput("@action.playSound.form.sound", "random.levelup", $this->getSound(), true),
                new ExampleNumberInput("@action.playSound.form.volume", "1", $this->getVolume(), true),
                new ExampleNumberInput("@action.playSound.form.pitch", "1", $this->getPitch(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5]];
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