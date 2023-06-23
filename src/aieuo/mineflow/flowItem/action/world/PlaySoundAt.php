<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use SOFe\AwaitGenerator\Await;

class PlaySoundAt extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position;

    public function __construct(
        string         $position = "",
        private string $sound = "",
        private string $volume = "1",
        private string $pitch = "1"
    ) {
        parent::__construct(self::PLAY_SOUND_AT, FlowItemCategory::WORLD);

        $this->position = new PositionPlaceholder("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "sound", "volume", "pitch"];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->getSound(), $this->getVolume(), $this->getPitch()];
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
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
        return $this->position->isNotEmpty() and $this->sound !== "" and $this->volume !== "" and $this->pitch !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $sound = $source->replaceVariables($this->getSound());
        $volume = $this->getFloat($source->replaceVariables($this->getVolume()));
        $pitch = $this->getFloat($source->replaceVariables($this->getPitch()));
        $position = $this->position->getPosition($source);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $position->x;
        $pk->y = $position->y;
        $pk->z = $position->z;
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        NetworkBroadcastUtils::broadcastPackets($position->world->getPlayers(), [$pk]);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            new ExampleInput("@action.playSound.form.sound", "random.levelup", $this->getSound(), true),
            new ExampleNumberInput("@action.playSound.form.volume", "1", $this->getVolume(), true),
            new ExampleNumberInput("@action.playSound.form.pitch", "1", $this->getPitch(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->setSound($content[1]);
        $this->setVolume($content[2]);
        $this->setPitch($content[3]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->getSound(), $this->getVolume(), $this->getPitch()];
    }
}
