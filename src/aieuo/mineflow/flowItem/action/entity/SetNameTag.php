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

    public function __construct(string $entity = "", string $newName = "") {
        parent::__construct(self::SET_NAME, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            StringArgument::create("name", $newName)->example("aieuo"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getNewName(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getNewName()->getString($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $entity->setNameTag($name);
        if ($entity instanceof Player) $entity->setDisplayName($name);

        yield Await::ALL;
    }
}
