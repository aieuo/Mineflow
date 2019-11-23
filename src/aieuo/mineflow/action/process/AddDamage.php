<?php

namespace aieuo\mineflow\action\process;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\Main;

class AddDamage extends Process {

    protected $id = self::ADD_DAMAGE;

    protected $name = "@action.addDamage.name";
    protected $description = "@action.addDamage.description";
    protected $detail = "action.addDamage.detail";

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

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Entity)) return null;
        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $damage = $this->getDamage();
        $cause = $this->getCause();
        if ($origin instanceof Recipe) {
            $damage = $origin->replaceVariables($damage);
        }

        if (!is_numeric($damage)) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        } elseif ((float)$damage < 1) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.addDamage.form.error")]));
            return null;
        }

        $event = new EntityDamageEvent($target, $cause, (float)$damage);
        $target->attack($event);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.addDamage.form.damage", Language::get("form.example", ["10"]), $default[1] ?? $this->getDamage()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $containsVariable = Main::getInstance()->getVariableHelper()->containsVariable($data[1]);
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif (!$containsVariable and !is_numeric($data[1])) {
            $errors[] = ["@mineflow.contents.notNumber", 1];
        } elseif (!$containsVariable and (float)$data[1] < 1) {
            $errors[] = ["@action.addDamage.form.error", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[0])) return null;
        $this->setDamage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getDamage()];
    }
}