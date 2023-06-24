<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\event\entity\EntityDamageEvent;
use SOFe\AwaitGenerator\Await;

class AddDamage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityPlaceholder $entity;

    public function __construct(
        string         $entity = "",
        private string $damage = "",
        private int    $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK
    ) {
        parent::__construct(self::ADD_DAMAGE, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "damage"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getDamage()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->getFloat($source->replaceVariables($this->getDamage()), min: 1);
        $cause = $this->getCause();
        $entity = $this->entity->getOnlineEntity($source);

        $event = new EntityDamageEvent($entity, $cause, $damage);
        $entity->attack($event);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleNumberInput("@action.addDamage.form.damage", "10", $this->getDamage(), true, 1),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setDamage($content[1]);
        if (isset($content[2])) $this->setCause((int)$content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getDamage()];
    }
}
