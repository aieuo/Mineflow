<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\event\entity\EntityDamageEvent;

class AddDamage extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::ADD_DAMAGE;

    protected string $name = "action.addDamage.name";
    protected string $detail = "action.addDamage.detail";
    protected array $detailDefaultReplace = ["entity", "damage"];

    protected string $category = Category::ENTITY;

    private string $damage;
    private int $cause;

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $damage = $source->replaceVariables($this->getDamage());
        $cause = $this->getCause();

        $this->throwIfInvalidNumber($damage, 1);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $event = new EntityDamageEvent($entity, $cause, (float)$damage);
        $entity->attack($event);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.addDamage.form.damage", "10", $this->getDamage(), true, 1),
        ];
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