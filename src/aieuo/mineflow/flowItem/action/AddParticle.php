<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Server;

class AddParticle extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::ADD_PARTICLE;

    protected $name = "action.addParticle.name";
    protected $detail = "action.addParticle.detail";
    protected $detailDefaultReplace = ["position", "particle", "amount", ""];

    protected $category = Category::LEVEL;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $particle;
    /** @var string */
    private $amount;

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
        /** @noinspection TypeUnsafeComparisonInspection */
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount(), $this->getAmount() == 1 ? "" : "s"]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $particleName = $origin->replaceVariables($this->getParticle());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 1);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        for ($i = 0; $i < (int)$amount; $i++) {
            $pk = new SpawnParticleEffectPacket();
            $pk->position = $position;
            $pk->particleName = $particleName;
            Server::getInstance()->broadcastPacket($position->level->getPlayers(), $pk);
        }
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PositionVariableDropdown($variables, $this->getPositionVariableName()),
                new ExampleInput("@action.addParticle.form.particle", "minecraft:explosion_particle", $this->getParticle(), true),
                new ExampleNumberInput("@action.addParticle.form.amount", "1", $this->getAmount(), true, 1),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
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