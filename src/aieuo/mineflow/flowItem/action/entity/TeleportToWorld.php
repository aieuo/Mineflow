<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class TeleportToWorld extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $name = "action.teleportToWorld.name";
    protected string $detail = "action.teleportToWorld.detail";
    protected array $detailDefaultReplace = ["entity", "world"];

    public function __construct(string $entity = "", private string $worldName = "", private bool $safeSpawn = true) {
        parent::__construct(self::TELEPORT_TO_WORLD, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
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
        return $this->getEntityVariableName() !== "" and $this->worldName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getWorldName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $worldName = $source->replaceVariables($this->getWorldName());

        $worldManager = Server::getInstance()->getWorldManager();
        $worldManager->loadWorld($worldName);
        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $pos = $this->safeSpawn ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.createPosition.form.world", "world", $this->getWorldName(), true),
            new Toggle("@action.teleportToWorld.form.safespawn", $this->isSafeSpawn()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setWorldName($content[1]);
        $this->setSafeSpawn($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getWorldName(), $this->isSafeSpawn()];
    }
}