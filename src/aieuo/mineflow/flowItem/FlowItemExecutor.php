<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UndefinedMineflowVariableException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;

class FlowItemExecutor {

    /* @var FlowItem[] */
    private array $items;
    private ?Entity $target;
    /* @var Variable[] */
    private array $variables;
    private ?FlowItemExecutor $parent;
    private ?Event $event;
    private ?\Closure $onComplete;
    private ?\Closure $onError;
    private ?Recipe $sourceRecipe;

    private mixed $lastResult;

    private FlowItem $currentFlowItem;
    private int $currentIndex;
    private \Generator $generator;

    private bool $waiting = false;
    private bool $exit = false;
    private bool $resuming = false;

    public function __construct(array $items, ?Entity $target, array $variables = [], ?self $parent = null, ?Event $event = null, \Closure $onComplete = null, \Closure $onError = null, ?Recipe $sourceRecipe = null) {
        $this->items = $items;
        $this->target = $target;
        $this->variables = $variables;
        $this->parent = $parent;
        $this->event = $event;
        $this->onComplete = $onComplete;
        $this->onError = $onError;
        $this->sourceRecipe = $sourceRecipe;

        if ($event === null and $parent !== null) {
            $this->event = $parent->getEvent();
        }
    }

    public function executeGenerator(): \Generator {
        foreach ($this->items as $i => $item) {
            $this->currentIndex = $i;
            $this->currentFlowItem = $item;
            $this->lastResult = yield from $item->execute($this);
        }
    }

    public function execute(bool $first = true): bool {
        $this->generator = $this->generator ?? $this->executeGenerator();

        try {
            if (!$first) $this->generator->next();

            while ($this->generator->valid()) {
                if ($this->exit) {
                    $this->resuming = false;
                    $this->waiting = false;
                    return false;
                }

                $result = $this->generator->current();
                if (!$result and !$this->resuming) {
                    $this->waiting = true;
                    return false;
                }

                if (!$result) {
                    $this->resuming = false;
                }

                $this->generator->next();
            }
        } catch (InvalidFlowValueException $e) {
            Logger::warning(Language::get("action.error", [$this->currentFlowItem->getName(), $e->getMessage()]), $this->target);
            if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
        } catch (UndefinedMineflowVariableException|UndefinedMineflowPropertyException|UndefinedMineflowMethodException|UnsupportedCalculationException $e) {
            if (!empty($e->getMessage())) Logger::warning($e->getMessage(), $this->target);
            if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
        }

        if ($this->onComplete !== null) ($this->onComplete)($this);
        return true;
    }

    public function resume(): void {
        $this->parent?->resume();

        $this->resuming = true;
        if (!$this->waiting) return;

        $this->resuming = false;
        $this->waiting = false;
        $this->execute(false);
    }

    public function exit(): void {
        $this->parent?->exit();

        $this->exit = true;
    }

    public function getTarget(): ?Entity {
        return $this->target;
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function getEvent(): ?Event {
        return $this->event;
    }

    public function getSourceRecipe(): ?Recipe {
        if ($this->parent !== null) return $this->parent->sourceRecipe;

        return $this->sourceRecipe;
    }

    public function replaceVariables(string $text): string {
        return Mineflow::getVariableHelper()->replaceVariables($text, $this->getVariables(), $this);
    }

    public function getVariable(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);

        $variable = $this->variables[$name] ?? ($this->parent?->getVariable($name));

        if ($variable === null) return null;

        foreach ($names as $name1) {
            if (!($variable instanceof ListVariable) and !($variable instanceof ObjectVariable)) return null;
            $variable = $variable->getValueFromIndex($name1);
        }
        return $variable;
    }

    public function getVariables(): array {
        $variables = $this->variables;
        if ($this->parent !== null) {
            $variables = array_merge($this->parent->getVariables(), $variables);
        }
        return $variables;
    }

    public function addVariable(string $name, Variable $variable, bool $onlyThisScope = false): void {
        $this->variables[$name] = $variable;

        if (!$onlyThisScope and $this->parent !== null) {
            $this->parent->addVariable($name, $variable);
        }
    }

    public function removeVariable(string $name): void {
        unset($this->variables[$name]);
        $this->parent?->removeVariable($name);
    }
}
