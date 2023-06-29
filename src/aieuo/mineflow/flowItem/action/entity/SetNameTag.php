<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetNameTag extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;
    private StringArgument $newName;

    public function __construct(string $entity = "", string $newName = "") {
        parent::__construct(self::SET_NAME, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->newName = new StringArgument("name", $newName, example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->newName->get()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getNewName(): StringArgument {
        return $this->newName;
    }

    public function isDataValid(): bool {
        return $this->entity->isValid() and $this->newName->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->newName->getString($source);
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
           $this->newName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->newName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->newName->get()];
    }
}
