<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\event\MineflowRecipeExecuteEvent;
use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\argument\FlowItemArrayArgument;
use aieuo\mineflow\flowItem\custom\CustomAction;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\argument\RecipeArgument;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\object\EventVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\RecipeVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function array_merge;
use function array_search;
use function array_splice;
use function array_unique;
use function array_values;
use function is_string;

class Recipe implements \JsonSerializable, FlowItemContainer {

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

    /** @var FlowItem[] */
    private array $actions = [];

    /** @var Trigger[] */
    private array $triggers = [];

    /** @var RecipeArgument[] */
    private array $arguments = [];
    private array $returnValues = [];

    protected ?FlowItemExecutor $executor;

    private array $lastPluginDependencies;
    private array $lastAddonDependencies;

    private string $rawData = "";

    private bool $enabled = true;
    private bool $readonly = false;

    public function __construct(string $name, string $group = "", string $author = "", string $pluginVersion = null) {
        $this->name = $name;
        $this->author = $author;
        $this->group = preg_replace("#/+#u", "/", $group);
        $this->version = $pluginVersion ?? Mineflow::getPluginVersion();
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

    public function setPluginVersion(string $version): void {
        $this->version = $version;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setReadonly(bool $readonly): void {
        $this->readonly = $readonly;
    }

    public function isReadonly(): bool {
        return $this->readonly;
    }

    public function setRawData(string $rawData): void {
        $this->rawData = $rawData;
    }

    public function getRawData(): string {
        return $this->rawData;
    }

    public function getLastPluginDependencies(): array {
        return $this->lastPluginDependencies;
    }

    public function getLastAddonDependencies(): array {
        return $this->lastAddonDependencies;
    }

    public function getDetail(): string {
        $details = [];
        foreach ($this->getTriggers() as $trigger) {
            $details[] = (string)$trigger;
        }
        $details[] = str_repeat("~", 20);
        foreach ($this->getActions() as $action) {
            $details[] = $action->getShortDetail();
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

    public function getContainerItemType(): string {
        return FlowItemContainer::ACTION;
    }

    public function addAction(FlowItem $action): void {
        $this->actions[] = $action;
    }

    /**
     * @param FlowItem[] $actions
     */
    public function setActions(array $actions): void {
        $this->actions = $actions;
    }

    public function pushAction(int $index, FlowItem $action): void {
        array_splice($this->actions, $index, 0, [$action]);
    }

    public function getAction(int $index): ?FlowItem {
        return $this->actions[$index] ?? null;
    }

    public function removeAction(int $index): void {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    /**
     * @return FlowItem[]
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * @return FlowItem[]
     */
    public function getActionsFlatten(): array {
        $flat = [];
        foreach ($this->getActions() as $item) {
            $flat[] = $item;
            foreach ($item->getArguments() as $argument) {
                if ($argument instanceof FlowItemArrayArgument) {
                    foreach ($argument->getItemsFlatten() as $item2) {
                        $flat[] = $item2;
                    }
                }
            }
        }
        return $flat;
    }

    public function addItem(FlowItem $item): void {
        $this->addAction($item);
    }

    /**
     * @param FlowItem[] $items
     */
    public function setItems(array $items): void {
        $this->setActions($items);
    }

    public function pushItem(int $index, FlowItem $item): void {
        $this->pushAction($index, $item);
    }

    public function getItem(int $index): ?FlowItem {
        return $this->getAction($index);
    }

    public function removeItem(int $index): void {
        $this->removeAction($index);
    }

    /**
     * @return FlowItem[]
     */
    public function getItems(): array {
        return $this->getActions();
    }

    /**
     * @return FlowItem[]
     */
    public function getItemsFlatten(): array {
        return $this->getActionsFlatten();
    }

    public function getTriggerHolder(): TriggerHolder {
        return TriggerHolder::global();
    }

    public function getTriggers(): array {
        return $this->triggers;
    }

    public function getTriggerByHash(string $type, string $hash): ?Trigger {
        foreach ($this->triggers as $trigger) {
            if ($trigger->getType() === $type and $trigger->hash() === $hash) {
                return $trigger;
            }
        }
        return null;
    }

    public function addTrigger(Trigger $trigger, bool $updateTriggerHolder = true): void {
        $this->triggers[] = $trigger;

        if ($updateTriggerHolder) {
            $this->getTriggerHolder()->addRecipe($trigger, $this);
        }
    }

    public function setTriggersFromArray(array $triggers, bool $updateTriggerHolder = true): void {
        $this->removeTriggerAll();
        foreach ($triggers as $triggerData) {
            try {
                $trigger = Triggers::deserialize($triggerData);
                if ($trigger === null) throw new \UnexpectedValueException(Language::get("trigger.notFound", [$triggerData["type"]]));
            } catch (\InvalidArgumentException $e) {
                throw new \UnexpectedValueException(Language::get("trigger.notFound", [$triggerData["type"]]), previous: $e);
            }
            $this->addTrigger($trigger, $updateTriggerHolder);
        }
    }

    public function existsTrigger(Trigger $trigger): bool {
        return in_array($trigger, $this->getTriggers());
    }

    public function removeTrigger(Trigger $trigger, bool $updateTriggerHolder = true): void {
        $index = array_search($trigger, $this->triggers, true);
        unset($this->triggers[$index]);
        $this->triggers = array_values($this->triggers);

        if ($updateTriggerHolder) {
            $this->getTriggerHolder()->removeRecipe($trigger, $this);
        }
    }

    public function removeTriggerAll(bool $updateTriggerHolder = true): void {
        foreach ($this->getTriggers() as $trigger) {
            $this->removeTrigger($trigger, $updateTriggerHolder);
        }
    }

    public function executeAllTargets(?Entity $player = null, array $variables = [], ?Event $event = null, array $args = [], ?FlowItemExecutor $from = null, ?callable $callback = null): ?bool {
        $targets = $this->getTargets($player);
        $variables = array_merge($variables, DefaultVariables::getServerVariables());

        foreach ($targets as $target) {
            $recipe = clone $this;
            $ev = new MineflowRecipeExecuteEvent($recipe, $target, $variables);
            $ev->call();
            if ($ev->isCancelled()) continue;

            $recipe->execute($target, $event, $ev->getVariables(), $args, $from, $callback);
        }
        return true;
    }

    public function getExecutor(): ?FlowItemExecutor {
        return $this->executor;
    }

    /**
     * @param Entity|null $target
     * @param Event|null $event
     * @param array<string, Variable> $variables
     * @param array $arguments
     * @return array<string, Variable>
     */
    protected function createVariables(?Entity $target, ?Event $event = null, array $variables = [], array $arguments = []): array {
        $helper = Mineflow::getVariableHelper();
        $args = array_values($arguments);
        foreach ($this->getArguments() as $i => $argument) {
            if (!isset($args[$i])) continue;

            $arg = $args[$i];
            if (!$arg instanceof Variable) {
                $arg = Variable::create($helper->currentType($arg), $helper->getType($arg));
            }
            try {
                $argument->validateType($arg);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(Language::get("recipe.argument.type.error", [$this->getPathname(), $argument->getName(), $argument->getType(), $arg::getTypeName()]), previous: $e);
            }
            $variables[$argument->getName()] = $arg;
        }

        if ($target !== null) {
            $variables = array_merge($variables, DefaultVariables::getEntityVariables($target));
        }
        if ($event !== null) {
            $variables["event"] = new EventVariable($event);
        }
        $variables["this"] = new RecipeVariable($this);
        $variables["_"] = $this->createSystemVariable($arguments);

        return $variables;
    }

    protected function createExecutor(array $actions, ?Entity $target, ?Event $event = null, array $variables = [], ?FlowItemExecutor $from = null, ?callable $callback = null): FlowItemExecutor {
        return new FlowItemExecutor($actions, $target, $variables, null, $event, function (FlowItemExecutor $executor) use($from, $callback) {
            if ($from !== null) {
                foreach ($this->getReturnValues() as $value) {
                    $name = $executor->replaceVariables($value);
                    $variable = $executor->getVariable($name);
                    if ($variable instanceof Variable) $from->addVariable($name, $variable);
                }
            }
            if ($callback !== null) {
                $callback($executor);
            }
        }, function (int $index, FlowItem $flowItem, ?Entity $target) {
            Logger::warning(Language::get("recipe.execute.failed", [$this->getPathname(), $index, $flowItem->getName()]), $target);
        }, $this);
    }

    public function execute(?Entity $target, ?Event $event = null, array $variables = [], array $arguments = [], ?FlowItemExecutor $from = null, ?callable $callback = null): bool {
        try {
            $variables = $this->createVariables($target, $event, $variables, $arguments);
        } catch (\InvalidArgumentException $e) {
            Logger::warning($e->getMessage(), $target);
            return false;
        }

        $this->executor = $this->createExecutor($this->getActions(), $target, $event, $variables, $from, $callback);
        $this->executor->execute();
        return true;
    }

    /**
     * @param array<string, Variable> $arguments
     * @return MapVariable
     */
    private function createSystemVariable(array $arguments): MapVariable {
        $values = [];
        foreach ($arguments as $name => $argument) {
            $values[] = new MapVariable([
                "name" => new StringVariable($name),
                "type" => new StringVariable($argument::getTypeName()),
                "value" => $argument
            ]);
        }
        $argumentVariable = new MapVariable($values);
        return new MapVariable(["args" => $argumentVariable]);
    }

    public function setArguments(array $arguments): void {
        $this->arguments = $arguments;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function addArgument(RecipeArgument $argument): void {
        $this->arguments[] = $argument;
    }

    public function removeArgument(RecipeArgument $argument): void {
        $index = array_search($argument, $this->arguments, true);
        if ($index !== false) {
            unset($this->arguments[$index]);
            $this->arguments = array_values($this->arguments);
        }
    }

    public function setReturnValues(array $returnValues): void {
        $this->returnValues = $returnValues;
    }

    public function getReturnValues(): array {
        return $this->returnValues;
    }

    /**
     * @param FlowItem $target
     * @return array|DummyVariable[]
     */
    public function getAddingVariablesUntil(FlowItem $target): array {
        $variables = [
            "target" => new DummyVariable(PlayerVariable::class)
        ];

        foreach ($this->getArguments() as $argument) {
            $variables[$argument->getName()] = $argument->getDummyVariable();
        }

        foreach ($this->getTriggers() as $trigger) {
            foreach ($trigger->getVariablesDummy() as $name => $variable) {
                $variables[$name] = $variable;
            }
        }

        foreach ($this->getActions() as $item) {
            foreach ($this->getFlowItemAddingVariablesUntil($item, $target) as $name => $variable) {
                $variables[$name] = $variable;
            }

            if ($item === $target) break;
        }
        return $variables;
    }

    private function getFlowItemAddingVariablesUntil(FlowItem $item, FlowItem $target): array {
        if ($item === $target) return [];

        $variablesMerge = [];
        foreach ($item->getArguments() as $argument) {
            if (!($argument instanceof FlowItemContainer)) continue;

            foreach ($argument->getItems() as $i) {
                $variablesMerge[] = $this->getFlowItemAddingVariablesUntil($i, $target);

                if ($i === $target) break 2;
            }
        }
        $variablesMerge[] = $item->getAddingVariables();

        return array_merge(...$variablesMerge);
    }

    public function getAddonDependencies(): array {
        $dependencies = [];
        foreach ($this->getActionsFlatten() as $action) {
            if ($action instanceof CustomAction) {
                $dependencies[] = $action->getAddonName();
            }
        }
        return array_values(array_unique($dependencies));
    }

    public function getPluginDependencies(): array {
        $dependencies = [];
        foreach ($this->getActionsFlatten() as $action) {
            if ($action->getPlugin() !== null) {
                $dependencies[] = $action->getPlugin()->getName();
            }
        }
        return array_values(array_unique($dependencies));
    }

    /**
     * @param array $contents
     * @return self
     * @throws FlowItemLoadException|\ErrorException
     */
    public function loadSaveData(array $contents): self {
        $actions = [];
        foreach ($contents["actions"] as $i => $content) {
            try {
                $action = FlowItem::loadEachSaveData($content);
            } catch (\ErrorException $e) {
                if (!str_starts_with($e->getMessage(), "Undefined offset:")) throw $e;
                throw new FlowItemLoadException(Language::get("recipe.load.failed.action", [$i, $content["id"] ?? "id?", ["recipe.json.key.missing"]]));
            }

            $actions[] = $action;
        }
        $this->setActions($actions);

        $this->setEnabled($contents["enabled"] ?? true);
        $this->setTargetSetting(
            $contents["target"]["type"] ?? Recipe::TARGET_DEFAULT,
            $contents["target"]["options"] ?? []
        );
        $this->setTriggersFromArray($contents["triggers"] ?? []);
        $this->setReturnValues($contents["returnValues"] ?? []);

        $arguments = [];
        foreach ($contents["arguments"] ?? [] as $argument) {
            if (is_string($argument)) {
                $arguments[] = new RecipeArgument(UnknownVariable::getTypeName(), $argument, "");
            } else {
                $arguments[] = RecipeArgument::unserialize($argument);
            }
        }
        $this->setArguments($arguments);

        $this->lastPluginDependencies = $contents["dependency"]["plugin"] ?? [];
        $this->lastAddonDependencies = $contents["dependency"]["addon"] ?? [];
        return $this;
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->name,
            "group" => $this->group,
            "plugin_version" => $this->version,
            "author" => $this->author,
            "enabled" => $this->enabled,
            "actions" => $this->getActions(),
            "triggers" => array_map(fn(Trigger $trigger) => Triggers::serialize($trigger), $this->triggers),
            "target" => [
                "type" => $this->targetType,
                "options" => $this->targetOptions,
            ],
            "arguments" => $this->getArguments(),
            "returnValues" => $this->getReturnValues(),
            "dependency" => [
                "addon" => $this->getAddonDependencies(),
                "plugin" => $this->getPluginDependencies(),
            ],
        ];
    }

    public function getFileName(string $baseDir): string {
        $group = Utils::getValidGroupName($this->getGroup());
        $name = Utils::getValidFileName($this->getName());
        if (!empty($group)) $baseDir = Path::join($baseDir, $group);
        return Path::join($baseDir, $name.".json");
    }

    public function save(string $dir): void {
        $path = $this->getFileName($dir);
        if (!file_exists(dirname($path))) @mkdir(dirname($path), 0777, true);

        $json = json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
        if ($json === $this->getRawData()) return;

        try {
            FileSystem::safeFilePutContents($path, $json);
            $this->setRawData($json);
        } catch(\RuntimeException $e) {
            Main::getInstance()->getLogger()->error(Language::get("recipe.save.failed", [$this->getPathname()]));
            Main::getInstance()->getLogger()->logException($e);
        }
    }

    public function unlink(string $dir): void {
        unlink($this->getFileName($dir));
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $action) {
            $actions[] = clone $action;
        }
        $this->setActions($actions);

        $arguments = [];
        foreach ($this->getArguments() as $k => $arg) {
            $arguments[$k] = clone $arg;
        }
        $this->setArguments($arguments);
    }
}