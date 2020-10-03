<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\event\entity\EntityDamageEvent;

class AddDamage extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::ADD_DAMAGE;

    protected $name = "action.addDamage.name";
    protected $detail = "action.addDamage.detail";
    protected $detailDefaultReplace = ["entity", "damage"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $damage;
    /** @var int */
    private $cause;

    public function __construct(string $entity = "", string $damage = "", int $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
        $this->setEntityVariableName($entity);
        $this->damage = $damage;
        $this->cause = $cause;
    }

    public function setDamage(string $damage): void {
        $this->damage = $damage;
    }

    public function getDamage(): string {
        return $this->damage;
    }

    public function setCause(int $cause): void {
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
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new ExampleNumberInput("@action.addDamage.form.damage", "10", $this->getDamage(), true, 1),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setDamage($content[1]);
        if (isset($content[2])) $this->setCause((int)$content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getDamage()];
    }
}