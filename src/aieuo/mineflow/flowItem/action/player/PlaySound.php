<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use SOFe\AwaitGenerator\Await;

class PlaySound extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $sound;
    private NumberArgument $volume;
    private NumberArgument $pitch;

    public function __construct(string         $player = "", string $sound = "", float          $volume = 1, float          $pitch = 1) {
        parent::__construct(self::PLAY_SOUND, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->sound = new StringArgument("sound", $sound, example: "random.levelup");
        $this->volume = new NumberArgument("volume", $volume, example: "1");
        $this->pitch = new NumberArgument("pitch", $pitch, example: "1");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "sound", "volume", "pitch"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->sound->get(), $this->volume->get(), $this->pitch->get()];
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getSound(): StringArgument {
        return $this->sound;
    }

    public function getVolume(): NumberArgument {
        return $this->volume;
    }

    public function getPitch(): NumberArgument {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->sound->isNotEmpty() and $this->volume->get() !== "" and $this->pitch->get() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $sound = $this->sound->getString($source);
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
            $this->sound->createFormElement($variables),
            $this->volume->createFormElement($variables),
            $this->pitch->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->sound->set($content[1]);
        $this->volume->set($content[2]);
        $this->pitch->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->sound->get(), $this->volume->get(), $this->pitch->get()];
    }
}
