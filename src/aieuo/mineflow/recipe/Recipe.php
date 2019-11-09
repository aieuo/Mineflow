<?php

namespace aieuo\mineflow\recipe;

use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\action\script\Script;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\utils\Logger;

class Recipe implements \JsonSerializable {

    const BLOCK = 0;
    const COMMAND = 1;
    const EVENT = 2;
    const CHAIN = 3;
    const FORM = 4;

    const CONTENT_TYPE_PROCESS = "action";
    const CONTENT_TYPE_CONDITION = "condition";
    const CONTENT_TYPE_SCRIPT = "script";

    const TARGET_DEFAULT = 0;
    const TARGET_SPECIFIED = 1;
    const TARGET_BROADCAST = 2;
    const TARGET_RANDOM = 3;
    const TARGET_NONE = 4;

    const TARGET_REQUIRED_NONE = "none";
    const TARGET_REQUIRED_ENTITY = "entity";
    const TARGET_REQUIRED_CREATURE = "creature";
    const TARGET_REQUIRED_PLAYER = "player";

    /** @var string */
    private $name;

    /** @var Action[] */
    private $actions = [];

    /** @var int */
    private $targetType = self::TARGET_DEFAULT;
    /** @var array */
    private $targetOptions = [];

    /** @var array */
    private $triggers = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDetail(): string {
        $details = [];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        return implode("\n", $details);
    }

    public function addAction(Action $action): void {
        $this->actions[] = $action;
    }

    public function getAction(int $index): ?Action {
        return $this->actions[$index] ?? null;
    }

    public function removeAction(int $index): void {
        unset($this->actions[$index]);
        $this->actions = array_merge($this->actions);
    }

    /**
     * @return Action[]
     */
    public function getActions(): array {
        return $this->actions;
    }

    public function setTarget(int $type, array $options): void {
        $this->targetType = $type;
        $this->targetOptions = $options;
    }

    public function getTargetType(): int {
        return $this->targetType;
    }

    public function getTargetOptions(): array {
        return $this->targetOptions;
    }

    public function getTargets(?Entity $player = null): array {
        $targets = [];
        switch ($this->targetType) {
            case self::TARGET_DEFAULT:
                $targets = [$player];
                break;
            case self::TARGET_SPECIFIED:
                $server = Server::getInstance();
                foreach ($this->targetOptions["specified"] as $targetName) {
                    $target = $server->getPlayer($targetName);
                    if (!($target instanceof Player)) continue;
                    $targets[] = $target;
                }
                break;
            case self::TARGET_BROADCAST:
                $targets = Server::getInstance()->getOnlinePlayers();
                break;
            case self::TARGET_RANDOM:
                $onlines = Server::getInstance()->getOnlinePlayers();
                foreach (array_rand($onlines, $this->targetOptions["random"]) as $key) {
                    $targets[] = $onlines[$key];
                }
                break;
            case self::TARGET_NONE:
                $targets = [null];
                break;
        }
        return $targets;
    }

    public function execute(?Entity $player = null): ?bool {
        $targets = $this->getTargets($player);
        foreach ($targets as $target) {
            foreach ($this->actions as $action) {
                $action->execute($target, $this);
            }
        }
        return true;
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->name,
            "actions" => $this->actions,
            "triggers" => $this->triggers,
            "targetType" => $this->targetType,
            "targetOptions" => $this->targetOptions
        ];
    }

    public function save(string $dir): void {
        file_put_contents($dir.$this->getName().".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
    }

    public function parseFromSaveData(array $datas): ?self {
        foreach ($datas as $i => $content) {
            switch ($content["type"]) {
                case self::CONTENT_TYPE_PROCESS:
                    $action = Process::parseFromSaveDataStatic($content);
                    break;
                case self::CONTENT_TYPE_SCRIPT:
                    $action = Script::parseFromSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) {
                Logger::warning(Language::get("recipe.load.faild.action", [$i, $content["id"] ?? "null", implode(",", $content["contents"] ?? ["null"])]));
                return null;
            }

            $this->addAction($action);
        }
        return $this;
    }
}