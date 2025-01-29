<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class PlaySound extends SimpleAction {

    public function __construct(string $player = "", string $sound = "", float $volume = 1, float $pitch = 1) {
        parent::__construct(self::PLAY_SOUND, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("sound", $sound)->example("random.levelup"),
            NumberArgument::create("volume", $volume)->example("1"),
            NumberArgument::create("pitch", $pitch)->example("1"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getSound(): StringArgument {
        return $this->getArgument("sound");
    }

    public function getVolume(): NumberArgument {
        return $this->getArgument("volume");
    }

    public function getPitch(): NumberArgument {
        return $this->getArgument("pitch");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $sound = $this->getSound()->getString($source);
        $volume = $this->getVolume()->getInt($source);
        $pitch = $this->getPitch()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

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
}