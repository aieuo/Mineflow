<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetYaw extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;

    public function __construct(string $entity = "", private string $yaw = "") {
        parent::__construct(self::SET_YAW, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "yaw"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getYaw()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function setYaw(string $yaw): void {
        $this->yaw = $yaw;
    }

    public function getYaw(): string {
        return $this->yaw;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->yaw !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $yaw = $this->getFloat($source->replaceVariables($this->getYaw()));
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setRotation($yaw, $entity->getLocation()->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $yaw, $entity->getLocation()->getPitch());

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleNumberInput("@action.setYaw.form.yaw", "180", $this->getYaw(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setYaw($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getYaw()];
    }
}
