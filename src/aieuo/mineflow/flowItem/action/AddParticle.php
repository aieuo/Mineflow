<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Server;

class AddParticle extends Action implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::ADD_PARTICLE;

    protected $name = "action.addParticle.name";
    protected $detail = "action.addParticle.detail";
    protected $detailDefaultReplace = ["position", "particle", "amount"];

    protected $category = Categories::CATEGORY_ACTION_LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $particle;
    /** @var string */
    private $amount;

    public function __construct(string $name = "pos", string $particle = "", string $amount = "1") {
        $this->positionVariableName = $name;
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
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getParticle()]);
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
                new Input("@flowItem.form.target.position", Language::get("form.example", ["pos"]), $default[1] ?? $this->getPositionVariableName()),
                new Input("@action.addParticle.form.particle", Language::get("form.example", ["minecraft:explosion_particle"]), $default[2] ?? $this->getParticle()),
                new Input("@action.addParticle.form.amount", Language::get("form.example", ["1"]), $default[3] ?? $this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "pos";
        $containsVariable = !Main::getVariableHelper()->containsVariable($data[3]);
        if ($data[3] === "") {
            $errors[] = ["@form.insufficient", 3];
        } elseif (!$containsVariable and !is_numeric($data[3])) {
            $errors[] = ["@flowItem.error.notNumber", 3];
        } elseif (!$containsVariable and (int)$data[3] < 1) {
            $errors[] = [Language::get("flowItem.error.lessValue", [1]), 3];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setPositionVariableName($content[0]);
        $this->setParticle($content[1]);
        $this->setAmount($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getParticle(), $this->getAmount()];
    }
}