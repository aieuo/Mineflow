<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class TeleportToWorld extends SimpleAction {

    public function __construct(string $entity = "", string $worldName = "", bool $safeSpawn = true) {
        parent::__construct(self::TELEPORT_TO_WORLD, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new StringArgument("world", $worldName, "@action.createPosition.form.world", example: "world"),
            new BooleanArgument("safespawn", $safeSpawn),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getWorldName(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getSafeSpawn(): BooleanArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $this->getWorldName()->getString($source);

        $worldManager = Server::getInstance()->getWorldManager();
        $worldManager->loadWorld($worldName);
        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $entity = $this->getEntity()->getOnlineEntity($source);

        $pos = $this->getSafeSpawn()->getBool() ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);

        yield Await::ALL;
    }
}
