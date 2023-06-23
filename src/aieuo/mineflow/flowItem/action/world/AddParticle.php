<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use SOFe\AwaitGenerator\Await;

class AddParticle extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position;

    public function __construct(
        string         $position = "",
        private string $particle = "",
        private string $amount = "1"
    ) {
        parent::__construct(self::ADD_PARTICLE, FlowItemCategory::WORLD);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->position = new PositionPlaceholder("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "particle", "amount", ""];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->getParticle(), $this->getAmount(), $this->getAmount() === "1" ? "" : "s"];
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function setParticle(string $particle): void {
        $this->particle = $particle;
    }

    public function getParticle(): string {
        return $this->particle;
    }

    public function setAmount(string $amount): void {
        $this->amount = $amount;
    }

    public function getAmount(): string {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->position->isNotEmpty() and $this->particle !== "" and $this->amount !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $particleName = $source->replaceVariables($this->getParticle());
        $amount = $this->getInt($source->replaceVariables($this->getAmount()), 1);

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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            new ExampleInput("@action.addParticle.form.particle", "minecraft:explosion_particle", $this->getParticle(), true),
            new ExampleNumberInput("@action.addParticle.form.amount", "1", $this->getAmount(), true, 1),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->setParticle($content[1]);
        $this->setAmount($content[2]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->getParticle(), $this->getAmount()];
    }
}
