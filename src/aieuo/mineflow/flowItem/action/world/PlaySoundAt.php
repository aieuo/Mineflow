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
use SOFe\AwaitGenerator\Await;

class PlaySoundAt extends SimpleAction {

    private PositionArgument $position;
    private StringArgument $sound;
    private NumberArgument $volume;
    private NumberArgument $pitch;

    public function __construct(string $position = "", string $sound = "", float $volume = 1, float $pitch = 1) {
        parent::__construct(self::PLAY_SOUND_AT, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->sound = new StringArgument("sound", $sound, example: "random.levelup"),
            $this->volume = new NumberArgument("volume", $volume, example: "1"),
            $this->pitch = new NumberArgument("pitch", $pitch, example: "1"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $sound = $this->sound->getString($source);
        $volume = $this->volume->getFloat($source);
        $pitch = $this->pitch->getFloat($source);
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
}
