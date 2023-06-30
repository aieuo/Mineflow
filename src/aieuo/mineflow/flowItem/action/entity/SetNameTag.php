<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetNameTag extends SimpleAction {

    private EntityArgument $entity;
    private StringArgument $newName;

    public function __construct(string $entity = "", string $newName = "") {
        parent::__construct(self::SET_NAME, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->newName = new StringArgument("name", $newName, example: "aieuo"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getNewName(): StringArgument {
        return $this->newName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->newName->getString($source);
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);

        yield Await::ALL;
    }
}
