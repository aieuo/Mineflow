<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\placeholder\NumberPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\flowItem\placeholder\StringPlaceholder;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use SOFe\AwaitGenerator\Await;

class AddParticle extends SimpleAction {

    private PositionPlaceholder $position;
    private StringPlaceholder $particle;
    private NumberPlaceholder $amount;

    public function __construct(string $position = "", string $particle = "", int $amount = 1) {
        parent::__construct(self::ADD_PARTICLE, FlowItemCategory::WORLD);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->position = new PositionPlaceholder("position", $position);
        $this->particle = new StringPlaceholder("particle", $particle, example: "minecraft:explosion_particle");
        $this->amount = new NumberPlaceholder("amount", $amount, example: "1", min: 1);
    }

    public function getDetailDefaultReplaces(): array {
        $replaces = parent::getDetailDefaultReplaces();
        $replaces[] = "";
        return $replaces;
    }

    public function getDetailReplaces(): array {
        $replaces = parent::getDetailReplaces();
        $replaces[] = $this->amount->get() === "1" ? "" : "s";
        return $replaces;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $particleName = $this->particle->getString($source);
        $amount = $this->amount->getInt($source);

        $position = $this->position->getPosition($source);

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
