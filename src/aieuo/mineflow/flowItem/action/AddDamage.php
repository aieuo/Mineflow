<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use pocketmine\event\entity\EntityDamageEvent;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class AddDamage extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::ADD_DAMAGE;

    protected $name = "action.addDamage.name";
    protected $detail = "action.addDamage.detail";
    protected $detailDefaultReplace = ["entity", "damage"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $damage;
    /** @var int */
    private $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK;

    public function __construct(string $name = "target", string $damage = "", int $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
        $this->entityVariableName = $name;
        $this->damage = $damage;
        $this->cause = $cause;
    }

    public function setDamage(string $damage) {
        $this->damage = $damage;
    }

    public function getDamage(): string {
        return $this->damage;
    }

    public function setCause(int $cause) {
        $this->cause = $cause;
    }

    public function getCause(): int {
        return $this->cause;
    }

    public function isDataValid(): bool {
        return $this->damage !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getDamage()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $damage = $origin->replaceVariables($this->getDamage());
        $cause = $this->getCause();

        $this->throwIfInvalidNumber($damage, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $event = new EntityDamageEvent($entity, $cause, (float)$damage);
        $entity->attack($event);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.addDamage.form.damage", Language::get("form.example", ["10"]), $default[2] ?? $this->getDamage()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $containsVariable = Main::getVariableHelper()->containsVariable($data[2]);
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!$containsVariable and !is_numeric($data[2])) {
            $errors[] = ["@flowItem.error.notNumber", 2];
        } elseif (!$containsVariable and (float)$data[2] < 1) {
            $errors[] = [Language::get("flowItem.error.lessValue", [1]), 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setDamage($content[1]);
        if (isset($content[2])) $this->setCause((int)$content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getDamage()];
    }
}