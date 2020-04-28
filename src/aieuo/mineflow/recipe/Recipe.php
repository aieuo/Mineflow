<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\action\ActionContainer;
use aieuo\mineflow\flowItem\action\ActionContainerTrait;
use aieuo\mineflow\flowItem\action\EventCancel;
use aieuo\mineflow\flowItem\action\Action;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\EventObjectVariable;
use aieuo\mineflow\variable\ObjectVariable;
use pocketmine\event\Event;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\Player;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\Main;

class Recipe implements \JsonSerializable, ActionContainer {
    use ActionContainerTrait;

    const CONTENT_TYPE_ACTION = "action";
    const CONTENT_TYPE_CONDITION = "condition";

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
    /* @var string */
    private $author;
    /* @var string */
    private $group;

    /** @var int */
    private $targetType = self::TARGET_DEFAULT;
    /** @var array */
    private $targetOptions = [];
    /** @var Entity|null */
    private $target = null;

    /** @var Trigger[] */
    private $triggers = [];

    /** @var bool|null */
    private $lastResult = null;

    /** @var array */
    private $variables = [];

    /** @var bool */
    private $wait = false;
    /** @var array|null */
    private $last = null;
    /** @var bool */
    private $exit = false;

    /** @var Recipe|null */
    private $sourceRecipe;
    /* @var array */
    private $arguments = [];
    /* @var array */
    private $returnValues = [];

    public function __construct(string $name, string $group = "", string $author = "") {
        $this->name = $name;
        $this->author = $author;
        $this->group = preg_replace("#/+#", "/", $group);
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getContainerName(): string {
        return $this->getName();
    }

    public function getGroup(): string {
        return $this->group;
    }

    public function getDetail(): string {
        $details = [];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        return implode("\n", $details);
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
                $count = (int)($this->targetOptions["random"] ?? 1);
                if ($count > count($onlines)) $count = count($onlines);
                $keys = $count <= 1 ? [array_rand($onlines)] : array_rand($onlines, $count);
                foreach ($keys as $key) {
                    $targets[] = $onlines[$key];
                }
                break;
            case self::TARGET_NONE:
                $targets = [null];
                break;
        }
        return $targets;
    }

    /**
     * @return Trigger[]
     */
    public function getTriggers(): array {
        return $this->triggers;
    }

    public function addTrigger(Trigger $trigger): void {
        TriggerHolder::getInstance()->addRecipe($trigger, $this);
        $this->triggers[] = $trigger;
    }

    public function setTriggersFromArray(array $triggers) {
        $this->removeTriggerAll();
        foreach ($triggers as $trigger) {
            $this->addTrigger(new Trigger($trigger["type"], $trigger["key"], $trigger["subKey"] ?? ""));
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

    public function removeTriggerAll() {
        foreach ($this->getTriggers() as $trigger) {
            $this->removeTrigger($trigger);
        }
    }

    public function executeAllTargets(?Entity $player = null, array $variables = [], ?Event $event = null, array $args = []): ?bool {
        // TODO: 整理する
        $targets = $this->getTargets($player);
        $variables = array_merge($variables, DefaultVariables::getServerVariables());
        if ($event instanceof Event) array_merge($variables, [new EventObjectVariable($event, "event")]);
        foreach ($targets as $target) {
            if ($target instanceof Entity) $variables = array_merge($variables, DefaultVariables::getEntityVariables($target));
            $recipe = clone $this;
            $recipe->setTarget($target)->addVariables($variables);
            $recipe->execute($target, $event, $args);
        }
        return true;
    }

    public function execute(?Entity $target, ?Event $event = null, array $args = [], int $start = 0): bool {
        $helper = Main::getVariableHelper();
        foreach ($this->getArguments() as $i => $argument) {
            if (isset($args[$i])) {
                $arg = $args[$i];
                if ($arg instanceof Variable) {
                    $arg->setName($argument);
                } else {
                    $type = $helper->getType($arg);
                    $arg = Variable::create($helper->currentType($arg), $argument, $type);
                }
                $this->addVariable($arg);
            }
        }

        $actions = $this->getActions();
        $count = count($actions);
        for ($i=$start; $i<$count; $i++) {
            if ($this->exit) break;

            $action = $actions[$i];
            if ($action instanceof EventCancel) $action->setEvent($event);
            try {
                $this->lastResult = $action->parent($this)->execute($this);
            } catch (\UnexpectedValueException $e) {
                if (!empty($e->getMessage())) Logger::warning($e->getMessage(), $target);
                Logger::warning(Language::get("recipe.execute.failed", [$this->getName(), $action->getName()]), $target);
                return false;
            }

            if ($this->wait) {
                $this->last = [$target, $event, $args, $i + 1];
                return true;
            }
        }

        if ($this->sourceRecipe instanceof Recipe) {
            foreach ($this->getReturnValues() as $value) {
                $variable = $this->getVariable($value);
                if ($variable instanceof Variable) $this->sourceRecipe->addVariable($variable);
            }
            $this->sourceRecipe->resume();
        }
        return true;
    }

    public function getTarget(): ?Entity {
        return $this->target;
    }

    public function setTarget(?Entity $target): self {
        $this->target = $target;
        return $this;
    }

    public function getLastActionResult(): ?bool {
        return $this->lastResult;
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

    public function addVariable(Variable $variable) {
        $this->variables[$variable->getName()] = $variable;
    }

    public function addVariables(array $variables) {
        $this->variables = array_merge($this->variables, $variables);
    }

    public function getVariable(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);
        if (!isset($this->variables[$name])) return null;

        $variable = $this->variables[$name];
        foreach ($names as $name) {
            if (!($variable instanceof ListVariable) and !($variable instanceof ObjectVariable)) return null;
            $variable = $variable->getValueFromIndex($name);
        }
        return $variable;
    }

    public function getVariables(): array {
        return $this->variables;
    }

    public function removeVariable(string $name) {
        unset($this->variables[$name]);
    }

    public function replaceVariables(string $text) {
        return Main::getVariableHelper()->replaceVariablesAndFunctions($text, $this);
    }

    public function setSourceRecipe(?Recipe $recipe) {
        $this->sourceRecipe = $recipe;
    }

    public function wait() {
        $this->wait = true;
    }

    public function resume() {
        $last = $this->last;
        if ($last === null) return;

        $this->wait = false;
        $this->last = null;

        $this->execute(...$last);
    }

    public function exit() {
        $this->exit = true;
    }

    /**
     * @param array $contents
     * @return self
     * @throws FlowItemLoadException
     * @throws \InvalidArgumentException
     */
    public function loadSaveData(array $contents): self {
        foreach ($contents as $i => $content) {
            if ($content["type"] !== self::CONTENT_TYPE_ACTION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

            try {
                $action = Action::loadSaveDataStatic($content);
            } catch (\OutOfBoundsException $e) {
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
            "author" => $this->author,
            "actions" => $this->actions,
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
        if (!empty($this->group)) $baseDir .= $this->group."/";
        return $baseDir.$this->getName().".json";
    }

    public function save(string $dir): void {
        $path = $this->getFileName($dir);
        if (!file_exists(dirname($path))) @mkdir(dirname($path), 0777, true);
        file_put_contents($path, json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
    }
}