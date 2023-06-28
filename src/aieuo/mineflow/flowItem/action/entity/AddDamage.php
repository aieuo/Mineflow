<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\event\entity\EntityDamageEvent;
use SOFe\AwaitGenerator\Await;

class AddDamage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;
    private NumberArgument $damage;

    public function __construct(
        string         $entity = "",
        string $damage = "",
        private int    $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK
    ) {
        parent::__construct(self::ADD_DAMAGE, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->damage = new NumberArgument("damage", $damage, example: "10", min: 1);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "damage"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->damage->get()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getDamage(): NumberArgument {
        return $this->damage;
    }

    public function setCause(int $cause): void {
        $this->cause = $cause;
    }

    public function getCause(): int {
        return $this->cause;
    }

    public function isDataValid(): bool {
        return $this->damage->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->damage->getFloat($source);
        $cause = $this->getCause();
        $entity = $this->entity->getOnlineEntity($source);

        $event = new EntityDamageEvent($entity, $cause, $damage);
        $entity->attack($event);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
           $this->damage->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->damage->set($content[1]);
        if (isset($content[2])) $this->setCause((int)$content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->damage->get()];
    }
}
