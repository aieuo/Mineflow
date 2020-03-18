<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\flowItem\action\ActionContainer;
use aieuo\mineflow\flowItem\action\ActionContainerTrait;
use aieuo\mineflow\flowItem\action\EventCancel;
use aieuo\mineflow\flowItem\action\Action;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
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

    const CONTENT_TYPE_PROCESS = "action";
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

    /** @var int */
    private $targetType = self::TARGET_DEFAULT;
    /** @var array */
    private $targetOptions = [];

    /** @var Trigger[] */
    private $triggers = [];

    /** @var bool|null */
    private $lastResult = null;

    /** @var array */
    private $variables = [];

    /** @var bool */
    private $wait = false;
    /** @var array */
    private $last;

    /** @var Recipe|null */
    private $sourceRecipe;
    /* @var array */
    private $arguments = [];
    /* @var array */
    private $returnValues = [];

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

    public function addTrigger(Trigger $trigger): void {
        TriggerHolder::getInstance()->addRecipe($trigger, $this);
        $this->triggers[] = $trigger;
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

    public function setTriggersFromArray(array $triggers) {
        $this->removeTriggerAll();
        foreach ($triggers as $trigger) {
            $this->addTrigger(new Trigger($trigger["type"], $trigger["key"]));
        }
    }

    public function existsTrigger(Trigger $trigger): bool {
        return in_array($trigger, $this->getTriggers());
    }

    /**
     * @return Trigger[]
     */
    public function getTriggers(): array {
        return $this->triggers;
    }

    public function executeAllTargets(?Entity $player = null, array $variables = [], ?Event $event = null): ?bool {
        $targets = $this->getTargets($player);
        foreach ($targets as $target) {
            $recipe = clone $this;
            $recipe->addVariables($variables);
            $recipe->execute($target, $event);
        }
        return true;
    }

    public function execute(?Entity $target, ?Event $event = null, array $args = [], int $start = 0): ?bool {
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
            $action = $actions[$i];
            if ($action instanceof EventCancel) $action->setEvent($event);
            $this->lastResult = $action->execute($target, $this);

            if ($this->lastResult === null) {
                Logger::warning(Language::get("recipe.execute.failed", [$this->getName(), $action->getName()]), $target);
                return false;
            }

            if ($this->wait) {
                $this->last = [$target, $event, $args, $i + 1];
                return true;
            }
        }
        if ($this->sourceRecipe instanceof Recipe) {
            foreach ($this->getReturnValues() as $i => $value) {
                $variable = $this->getVariable($value);
                if ($variable instanceof Variable) $this->sourceRecipe->addVariable($variable);
            }
            $this->sourceRecipe->resume();
        }
        return true;
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
        return $this->variables[$name] ?? null;
    }

    public function getVariables(): array {
        return $this->variables;
    }

    public function removeVariable(string $name) {
        unset($this->variables[$name]);
    }

    public function replaceVariables(string $text) {
        return Main::getVariableHelper()->replaceVariables($text, $this->variables);
    }

    public function setSourceRecipe(?Recipe $recipe) {
        $this->sourceRecipe = $recipe;
    }

    public function wait() {
        $this->wait = true;
    }

    public function resume() {
        $this->wait = false;
        if ($this->last === null) return;
        $this->execute(...$this->last);
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->name,
            "actions" => $this->actions,
            "triggers" => $this->triggers,
            "targetType" => $this->targetType,
            "targetOptions" => $this->targetOptions,
            "arguments" => $this->getArguments(),
            "returnValues" => $this->getReturnValues(),
        ];
    }

    public function save(string $dir): void {
        file_put_contents($dir.$this->getName().".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
    }

    public function loadSaveData(array $contents): ?self {
        foreach ($contents as $i => $content) {
            switch ($content["type"]) {
                case self::CONTENT_TYPE_PROCESS:
                    $action = Action::loadSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) {
                Logger::warning(Language::get("recipe.load.failed.action", [$i, $content["id"] ?? "id?"]));
                return null;
            }

            $this->addAction($action);
        }
        return $this;
    }
}