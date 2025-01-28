<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class PlaySoundAt extends SimpleAction {

    public function __construct(string $position = "", string $sound = "", float $volume = 1, float $pitch = 1) {
        parent::__construct(self::PLAY_SOUND_AT, FlowItemCategory::WORLD);

        $this->setArguments([
            PositionArgument::create("position", $position),
            StringArgument::create("sound", $sound)->example("random.levelup"),
            NumberArgument::create("volume", $volume)->example("1"),
            NumberArgument::create("pitch", $pitch)->example("1"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
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
        $volume = $this->getVolume()->getFloat($source);
        $pitch = $this->getPitch()->getFloat($source);
        $position = $this->getPosition()->getPosition($source);

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
}