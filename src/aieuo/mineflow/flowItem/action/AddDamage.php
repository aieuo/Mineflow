<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use pocketmine\event\entity\EntityDamageEvent;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

class AddDamage extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::ADD_DAMAGE;

    protected $name = "action.addDamage.name";
    protected $detail = "action.addDamage.detail";
    protected $detailDefaultReplace = ["entity", "damage"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $damage;
    /** @var int */
    private $cause;

    public function __construct(string $entity = "target", string $damage = "", int $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
        $this->setEntityVariableName($entity);
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $damage = $origin->replaceVariables($this->getDamage());
        $cause = $this->getCause();

        $this->throwIfInvalidNumber($damage, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $event = new EntityDamageEvent($entity, $cause, (float)$damage);
        $entity->attack($event);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.entity", "target", $default[1] ?? $this->getEntityVariableName(), true),
                new ExampleNumberInput("@action.addDamage.form.damage", "10", $default[2] ?? $this->getDamage(), true, 1),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        $this->setEntityVariableName($content[0]);
        $this->setDamage($content[1]);
        if (isset($content[2])) $this->setCause((int)$content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getDamage()];
    }
}