<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Server;

class AddParticle extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected string $id = self::ADD_PARTICLE;

    protected string $name = "action.addParticle.name";
    protected string $detail = "action.addParticle.detail";
    protected array $detailDefaultReplace = ["position", "particle", "amount", ""];

    protected string $category = FlowItemCategory::WORLD;

    protected int $permission = self::PERMISSION_LEVEL_1;

    private string $particle;
    private string $amount;

    public function __construct(string $position = "", string $particle = "", string $amount = "1") {
        $this->setPositionVariableName($position);
        $this->particle = $particle;
        $this->amount = $amount;
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
        return $this->getPositionVariableName() !== "" and $this->particle !== "" and $this->amount !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount(), $this->getAmount() === "1" ? "" : "s"]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $particleName = $source->replaceVariables($this->getParticle());
        $amount = $source->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 1);

        $position = $this->getPosition($source);

        for ($i = 0; $i < (int)$amount; $i++) {
            $pk = new SpawnParticleEffectPacket();
            $pk->position = $position;
            $pk->particleName = $particleName;
            Server::getInstance()->broadcastPackets($position->world->getPlayers(), [$pk]);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleInput("@action.addParticle.form.particle", "minecraft:explosion_particle", $this->getParticle(), true),
            new ExampleNumberInput("@action.addParticle.form.amount", "1", $this->getAmount(), true, 1),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setParticle($content[1]);
        $this->setAmount($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount()];
    }
}