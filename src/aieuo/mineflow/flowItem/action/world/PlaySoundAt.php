<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\NumberPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\flowItem\placeholder\StringPlaceholder;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use SOFe\AwaitGenerator\Await;

class PlaySoundAt extends SimpleAction {

    private PositionPlaceholder $position;
    private StringPlaceholder $sound;
    private NumberPlaceholder $volume;
    private NumberPlaceholder $pitch;

    public function __construct(string $position = "", string $sound = "", float $volume = 1, float $pitch = 1) {
        parent::__construct(self::PLAY_SOUND_AT, FlowItemCategory::WORLD);

        $this->setPlaceholders([
            $this->position = new PositionPlaceholder("position", $position),
            $this->sound = new StringPlaceholder("sound", $sound, example: "random.levelup"),
            $this->volume = new NumberPlaceholder("volume", $volume, example: "1"),
            $this->pitch = new NumberPlaceholder("pitch", $pitch, example: "1"),
        ]);
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function getSound(): StringPlaceholder {
        return $this->sound;
    }

    public function getVolume(): NumberPlaceholder {
        return $this->volume;
    }

    public function getPitch(): NumberPlaceholder {
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
