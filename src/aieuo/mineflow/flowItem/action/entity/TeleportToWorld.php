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

    private EntityArgument $entity;
    private StringArgument $worldName;
    private BooleanArgument $safeSpawn;

    public function __construct(string $entity = "", string $worldName = "", bool $safeSpawn = true) {
        parent::__construct(self::TELEPORT_TO_WORLD, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->worldName = new StringArgument("world", $worldName, "@action.createPosition.form.world", example: "world"),
            $this->safeSpawn = new BooleanArgument("safespawn", $safeSpawn),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getWorldName(): StringArgument {
        return $this->worldName;
    }

    public function getSafeSpawn(): BooleanArgument {
        return $this->safeSpawn;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $this->worldName->getString($source);

        $worldManager = Server::getInstance()->getWorldManager();
        $worldManager->loadWorld($worldName);
        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $entity = $this->entity->getOnlineEntity($source);

        $pos = $this->safeSpawn->getBool() ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);

        yield Await::ALL;
    }
}
