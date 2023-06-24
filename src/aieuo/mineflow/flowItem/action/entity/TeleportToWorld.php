<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class TeleportToWorld extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityPlaceholder $entity;

    public function __construct(string $entity = "", private string $worldName = "", private bool $safeSpawn = true) {
        parent::__construct(self::TELEPORT_TO_WORLD, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getWorldName()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setWorldName(string $worldName): void {
        $this->worldName = $worldName;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function isSafeSpawn(): bool {
        return $this->safeSpawn;
    }

    public function setSafeSpawn(bool $safeSpawn): void {
        $this->safeSpawn = $safeSpawn;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->worldName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $source->replaceVariables($this->getWorldName());

        $worldManager = Server::getInstance()->getWorldManager();
        $worldManager->loadWorld($worldName);
        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $entity = $this->entity->getOnlineEntity($source);

        $pos = $this->safeSpawn ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleInput("@action.createPosition.form.world", "world", $this->getWorldName(), true),
            new Toggle("@action.teleportToWorld.form.safespawn", $this->isSafeSpawn()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setWorldName($content[1]);
        $this->setSafeSpawn($content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getWorldName(), $this->isSafeSpawn()];
    }
}
