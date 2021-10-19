<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class TeleportToWorld extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::TELEPORT_TO_WORLD;

    protected string $name = "action.teleportToWorld.name";
    protected string $detail = "action.teleportToWorld.detail";
    protected array $detailDefaultReplace = ["entity", "world"];

    protected string $category = Category::ENTITY;

    private string $worldName;
    private bool $safeSpawn;

    public function __construct(string $entity = "", string $worldName = "", bool $safeSpawn = true) {
        $this->setEntityVariableName($entity);
        $this->worldName = $worldName;
        $this->safeSpawn = $safeSpawn;
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
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getWorldName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $worldName = $source->replaceVariables($this->getWorldName());

        Server::getInstance()->loadLevel($worldName);
        $world = Server::getInstance()->getLevelByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPositionVariable.world.notFound"));
        }

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $pos = $this->safeSpawn ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.createPositionVariable.form.world", "world", $this->getWorldName(), true),
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