<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\event\MineflowRecipeExecuteEvent;
use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EventObjectVariable;
use aieuo\mineflow\variable\object\RecipeObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use function array_key_last;
use function explode;
use function str_replace;
use function version_compare;

class Recipe implements \JsonSerializable, FlowItemContainer {
    use FlowItemContainerTrait {
        getAddingVariablesBefore as traitGetAddingVariableBefore;
    }

    public const TARGET_NONE = 0;
    public const TARGET_DEFAULT = 1;
    public const TARGET_SPECIFIED = 2;
    public const TARGET_ON_WORLD = 3;
    public const TARGET_BROADCAST = 4;
    public const TARGET_RANDOM = 5;

    private string $name;
    private string $author;
    private string $group;
    private ?string $version;

    private int $targetType = self::TARGET_DEFAULT;
    private array $targetOptions = [];

    /** @var Trigger[] */
    private array $triggers = [];

    private array $arguments = [];
    private array $returnValues = [];

    private ?FlowItemExecutor $executor;

    private string $rawData = "";

    public function __construct(string $name, string $group = "", string $author = "", string $pluginVersion = null) {
        $this->name = $name;
        $this->author = $author;
        $this->group = preg_replace("#/+#u", "/", $group);
        $this->version = $pluginVersion ?? Mineflow::pluginVersion();
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPathname(): string {
        return empty($this->getGroup()) ? $this->getName() : ($this->getGroup()."/".$this->getName());
    }

    public function getContainerName(): string {
        return $this->getName();
    }

    public function setGroup(string $group): void {
        $this->group = preg_replace("#/+#u", "/", $group);
    }

    public function getGroup(): string {
        return $this->group;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getPluginVersion(): string {
        return $this->version;
    }

    public function setRawData(string $rawData): void {
        $this->rawData = $rawData;
    }

    public function getRawData(): string {
        return $this->rawData;
    }

    public function getDetail(): string {
        $details = [];
        foreach ($this->getTriggers() as $trigger) {
            $details[] = (string)$trigger;
        }
        $details[] = str_repeat("~", 20);
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        return implode("\n§f", $details);
    }

    public function setTargetSetting(int $type, array $options = []): void {
        $this->targetType = $type;
        $this->targetOptions = array_merge($this->targetOptions, $options);
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
            case self::TARGET_NONE:
                $targets = [null];
                break;
            case self::TARGET_DEFAULT:
                $targets = [$player];
                break;
            case self::TARGET_SPECIFIED:
                $server = Server::getInstance();
                foreach ($this->targetOptions["specified"] as $targetName) {
                    $target = $server->getPlayerExact($targetName);
                    if (!($target instanceof Player)) continue;
                    $targets[] = $target;
                }
                break;
            case self::TARGET_ON_WORLD:
                if ($player === null) return [];
                $targets = $player->getWorld()->getPlayers();
                break;
            case self::TARGET_BROADCAST:
                $targets = Server::getInstance()->getOnlinePlayers();
                break;
            case self::TARGET_RANDOM:
                $onlines = Server::getInstance()->getOnlinePlayers();
                $count = (int)($this->targetOptions["random"] ?? 1);
                if ($count > count($onlines)) $count = count($onlines);
                $keys = $count <= 1 ? [array_rand($onlines)] : array_rand($onlines, $count);
                foreach ($keys as $key) {
                    $targets[] = $onlines[$key];
                }
                break;
        }
        return $targets;
    }

    public function getTriggers(): array {
        return $this->triggers;
    }

    public function addTrigger(Trigger $trigger): void {
        TriggerHolder::getInstance()->addRecipe($trigger, $this);
        $this->triggers[] = $trigger;
    }

    public function setTriggersFromArray(array $triggers): void {
        $this->removeTriggerAll();
        foreach ($triggers as $triggerData) {
            $trigger = Triggers::getTrigger($triggerData["type"], $triggerData["key"], $triggerData["subKey"] ?? "");
            if ($trigger === null) throw new \UnexpectedValueException(Language::get("trigger.notFound", [$triggerData["type"]]));
            $this->addTrigger($trigger);
        }
    }

    public function existsTrigger(Trigger $trigger): bool {
        return in_array($trigger, $this->getTriggers());
    }

    public function removeTrigger(Trigger $trigger): void {
        TriggerHolder::getInstance()->removeRecipe($trigger, $this);
        $index = array_search($trigger, $this->triggers, true);
        unset($this->triggers[$index]);
        $this->triggers = array_values($this->triggers);
    }

    public function removeTriggerAll(): void {
        foreach ($this->getTriggers() as $trigger) {
            $this->removeTrigger($trigger);
        }
    }

    public function executeAllTargets(?Entity $player = null, array $variables = [], ?Event $event = null, array $args = [], ?FlowItemExecutor $callbackExecutor = null): ?bool {
        $targets = $this->getTargets($player);
        $variables = array_merge($variables, DefaultVariables::getServerVariables());

        foreach ($targets as $target) {
            $recipe = clone $this;
            $ev = new MineflowRecipeExecuteEvent($recipe, $target, $variables);
            $ev->call();
            if ($ev->isCancelled()) continue;

            $recipe->execute($target, $event, $ev->getVariables(), $args, $callbackExecutor);
        }
        return true;
    }

    public function getExecutor(): ?FlowItemExecutor {
        return $this->executor;
    }

    public function execute(?Entity $target, ?Event $event = null, array $variables = [], array $args = [], ?FlowItemExecutor $callbackExecutor = null): bool {
        $helper = Main::getVariableHelper();
        foreach ($this->getArguments() as $i => $argument) {
            if (!isset($args[$i])) continue;

            $arg = $args[$i];
            if (!$arg instanceof Variable) {
                $arg = Variable::create($helper->currentType($arg), $helper->getType($arg));
            }
            $variables[$argument] = $arg;
        }
        if ($target !== null) {
            $variables = array_merge($variables, DefaultVariables::getEntityVariables($target));
        }
        if ($event !== null) {
            $variables["event"] = new EventObjectVariable($event);
        }
        $variables["this"] = new RecipeObjectVariable($this);

        $this->executor = new FlowItemExecutor($this->getActions(), $target, $variables, null, $event, function (FlowItemExecutor $executor) use($callbackExecutor) {
            if ($callbackExecutor !== null) {
                foreach ($this->getReturnValues() as $value) {
                    $variable = $executor->getVariable($value);
                    if ($variable instanceof Variable) $callbackExecutor->addVariable($value, $variable);
                }
                $callbackExecutor->resume();
            }
        }, function (int $index, FlowItem $flowItem, ?Entity $target) {
            Logger::warning(Language::get("recipe.execute.failed", [$this->getPathname(), $index, $flowItem->getName()]), $target);
        }, $this);
        $this->executor->execute();
        return true;
    }

    public function setArguments(array $arguments): void {
        $this->arguments = $arguments;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function setReturnValues(array $returnValues): void {
        $this->returnValues = $returnValues;
    }

    public function getReturnValues(): array {
        return $this->returnValues;
    }

    public function getAddingVariablesBefore(FlowItem $flowItem, array $containers, string $type): array {
        $variables = [
            "target" => new DummyVariable(DummyVariable::PLAYER)
        ];

        $add = [];
        foreach ($this->getTriggers() as $trigger) {
            $add[] = $trigger->getVariablesDummy();
        }
        return array_merge(array_merge($variables, ...$add), $this->traitGetAddingVariableBefore($flowItem, $containers, $type));
    }

    /**
     * @param array $contents
     * @return self
     * @throws FlowItemLoadException|\ErrorException
     */
    public function loadSaveData(array $contents): self {
        foreach ($contents as $i => $content) {
            try {
                $action = FlowItem::loadEachSaveData($content);
            } catch (\ErrorException $e) {
                if (!str_starts_with($e->getMessage(), "Undefined offset:")) throw $e;
                throw new FlowItemLoadException(Language::get("recipe.load.failed.action", [$i, $content["id"] ?? "id?", ["recipe.json.key.missing"]]));
            }

            $this->addAction($action);
        }
        return $this;
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->name,
            "group" => $this->group,
            "plugin_version" => $this->version,
            "author" => $this->author,
            "actions" => $this->getActions(),
            "triggers" => $this->triggers,
            "target" => [
                "type" => $this->targetType,
                "options" => $this->targetOptions,
            ],
            "arguments" => $this->getArguments(),
            "returnValues" => $this->getReturnValues(),
        ];
    }

    public function getFileName(string $baseDir): string {
        $group = preg_replace("#[.¥:?<>|*\"]#u", "", $this->getGroup());
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", $this->getName());
        if (!empty($group)) $baseDir .= $group."/";
        return $baseDir.$name.".json";
    }

    public function save(string $dir): void {
        $path = $this->getFileName($dir);
        if (!file_exists(dirname($path))) @mkdir(dirname($path), 0777, true);

        $json = json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
        if ($json === $this->getRawData()) return;

        try {
            FileSystem::safeFilePutContents($path, $json);
        } catch(\RuntimeException $e) {
            Main::getInstance()->getLogger()->error(Language::get("recipe.save.failed", [$this->getPathname()]));
            Main::getInstance()->getLogger()->logException($e);
        }
    }

    public function checkVersion(): void {
        $createdVersion = $this->version;
        $currentVersion = Main::getPluginVersion();

        if ($createdVersion !== null and version_compare($createdVersion, $currentVersion, "=")) return;

        $this->upgrade($createdVersion, $currentVersion);
    }

    public function upgrade(?string $from, string $to): void {
        if ($this->needUpgrade($from, $to, "2.0.0")) {
            $oldToNewTargetMap = [
                4 => self::TARGET_NONE,
                0 => self::TARGET_DEFAULT,
                1 => self::TARGET_SPECIFIED,
                2 => self::TARGET_BROADCAST,
                3 => self::TARGET_RANDOM,
            ];
            if (isset($oldToNewTargetMap[$this->targetType])) {
                $this->targetType = $oldToNewTargetMap[$this->targetType];
            }
            foreach ($this->flattenFlowItems($this, FlowItemContainer::ACTION) as $action) {
                $this->replaceLevelToWorld($action);
            }
            foreach ($this->flattenFlowItems($this, FlowItemContainer::CONDITION) as $condition) {
                $this->replaceLevelToWorld($condition);
            }

            $from = "2.0.0";
        }
        if ($this->needUpgrade($from, $to, "2.6.0")) {
            $eventTriggers = Main::getEventManager();
            foreach ($this->getTriggers() as $trigger) {
                if ($trigger instanceof EventTrigger) {
                    $this->removeTrigger($trigger);
                    $tmp = explode("\\", str_replace("/", "\\", $trigger->getKey()));
                    $key = $tmp[array_key_last($tmp)];
                    $this->addTrigger($eventTriggers->getTrigger($key, $trigger->getSubKey()));
                }
            }

            $from = "2.6.0";
        }

        $this->version = $from;
    }

    private function needUpgrade(?string $from, string $current, string $target): bool {
        return version_compare($target, $current, "<=") and ($from === null or version_compare($from, $target, "<"));
    }

    private function replaceLevelToWorld(FlowItem $action): void {
        $newContents = [];
        foreach ($action->serializeContents() as $data) {
            if (is_string($data)) {
                $data = str_replace(["origin_level", "target_level"], ["origin_world", "target_world"], $data);
                $data = preg_replace("/({.+\.)level((\.?.+)*})/u", "$1world$2", $data);
            }
            $newContents[] = $data;
        }
        $action->loadSaveData($newContents);
    }

    /**
     * @param FlowItemContainer $container
     * @param string $type
     * @return FlowItem[]
     */
    public function flattenFlowItems(FlowItemContainer $container, string $type): array {
        $flat = [];
        foreach ($container->getItems($type) as $item) {
            if ($item instanceof FlowItemContainer) {
                foreach ($this->flattenFlowItems($item, $type) as $item2) {
                    $flat[] = $item2;
                }
            } else {
                $flat[] = $item;
            }
        }
        return $flat;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
