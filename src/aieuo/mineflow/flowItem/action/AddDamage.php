<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class AddDamage extends Action {

    protected $id = self::ADD_DAMAGE;

    protected $name = "action.addDamage.name";
    protected $detail = "action.addDamage.detail";
    protected $detailDefaultReplace = ["damage"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $damage;
    /** @var int */
    private $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK;

    public function __construct(string $damage = "", int $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
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
        return Language::get($this->detail, [$this->getDamage()]);
    }

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $damage = $origin->replaceVariables($this->getDamage());
        $cause = $this->getCause();

        $this->throwIfInvalidNumber($damage, 1, null);

        $event = new EntityDamageEvent($target, $cause, (float)$damage);
        $target->attack($event);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.addDamage.form.damage", Language::get("form.example", ["10"]), $default[1] ?? $this->getDamage()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $containsVariable = Main::getVariableHelper()->containsVariable($data[1]);
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif (!$containsVariable and !is_numeric($data[1])) {
            $errors[] = ["@flowItem.error.notNumber", 1];
        } elseif (!$containsVariable and (float)$data[1] < 1) {
            $errors[] = [Language::get("flowItem.error.lessValue", [1]), 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setDamage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getDamage()];
    }
}