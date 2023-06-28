<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use SOFe\AwaitGenerator\Await;

class PlaySound extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private NumberArgument $volume;
    private NumberArgument $pitch;

    public function __construct(
        string         $player = "",
        private string $sound = "",
        float          $volume = 1,
        float          $pitch = 1
    ) {
        parent::__construct(self::PLAY_SOUND, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->volume = new NumberArgument("volume", $volume, example: "1");
        $this->pitch = new NumberArgument("pitch", $pitch, example: "1");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "sound", "volume", "pitch"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getSound(), $this->volume->get(), $this->pitch->get()];
    }

    public function setSound(string $health): void {
        $this->sound = $health;
    }

    public function getSound(): string {
        return $this->sound;
    }

    public function getVolume(): NumberArgument {
        return $this->volume;
    }

    public function getPitch(): NumberArgument {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->sound !== "" and $this->volume->get() !== "" and $this->pitch->get() !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $sound = $source->replaceVariables($this->getSound());
        $volume = $this->volume->getInt($source);
        $pitch = $this->pitch->getInt($source);
        $player = $this->player->getOnlinePlayer($source);

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $player->getLocation()->getX();
        $pk->y = $player->getLocation()->getY();
        $pk->z = $player->getLocation()->getZ();
        $pk->volume = (float)$volume;
        $pk->pitch = (float)$pitch;
        $player->getNetworkSession()->sendDataPacket($pk);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.playSound.form.sound", "random.levelup", $this->getSound(), true),
            $this->volume->createFormElement($variables),
            $this->pitch->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setSound($content[1]);
        $this->volume->set($content[2]);
        $this->pitch->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getSound(), $this->volume->get(), $this->pitch->get()];
    }
}
