<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use SOFe\AwaitGenerator\Await;

class AddParticle extends SimpleAction {

    public function __construct(string $position = "", string $particle = "", int $amount = 1) {
        parent::__construct(self::ADD_PARTICLE, FlowItemCategory::WORLD);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->setArguments([
            PositionArgument::create("position", $position),
            StringArgument::create("particle", $particle)->example("minecraft:explosion_particle"),
            NumberArgument::create("amount", $amount)->min(1)->example("1"),
        ]);
    }

    public function getDetailDefaultReplaces(): array {
        $replaces = parent::getDetailDefaultReplaces();
        $replaces[] = "";
        return $replaces;
    }

    public function getDetailReplaces(): array {
        $replaces = parent::getDetailReplaces();
        $replaces[] = $this->getAmount()->getRawString() === "1" ? "" : "s";
        return $replaces;
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[0];
    }

    public function getParticle(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getAmount(): NumberArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $particleName = $this->getParticle()->getString($source);
        $amount = $this->getAmount()->getInt($source);

        $position = $this->getPosition()->getPosition($source);

        for ($i = 0; $i < $amount; $i++) {
            $pk = new SpawnParticleEffectPacket();
            $pk->position = $position;
            $pk->particleName = $particleName;
            $pk->molangVariablesJson = "";
            NetworkBroadcastUtils::broadcastPackets($position->world->getPlayers(), [$pk]);
        }

        yield Await::ALL;
    }
}
