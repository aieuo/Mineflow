<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Server;

class AddParticle extends Action implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::ADD_PARTICLE;

    protected $name = "action.addParticle.name";
    protected $detail = "action.addParticle.detail";
    protected $detailDefaultReplace = ["position", "particle", "amount", ""];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $particle;
    /** @var string */
    private $amount;

    public function __construct(string $position = "pos", string $particle = "", string $amount = "1") {
        $this->setPositionVariableName($position);
        $this->particle = $particle;
        $this->amount = $amount;
    }

    public function setParticle(string $particle) {
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
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount(), $this->getAmount() == 1 ? "" : "s"]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $particleName = $origin->replaceVariables($this->getParticle());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 1);

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        for ($i=0; $i<(int)$amount; $i++) {
            $pk = new SpawnParticleEffectPacket();
            $pk->position = $position;
            $pk->particleName = $particleName;
            Server::getInstance()->broadcastPacket($position->level->getPlayers(), $pk);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.position", "pos", $default[1] ?? $this->getPositionVariableName(), true),
                new ExampleInput("@action.addParticle.form.particle", "minecraft:explosion_particle", $default[2] ?? $this->getParticle(), true),
                new ExampleNumberInput("@action.addParticle.form.amount", "1", $default[3] ?? $this->getAmount(), true, 1),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        $this->setPositionVariableName($content[0]);
        $this->setParticle($content[1]);
        $this->setAmount($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount()];
    }
}