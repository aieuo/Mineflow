<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\exception\MineflowException;
use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;
use function count;

class FlowItemExecutor {

    private mixed $lastResult;

    private FlowItem $currentFlowItem;
    private int $currentIndex;

    private VariableRegistry $variableRegistry;

    /**
     * @param FlowItem[] $items
     * @param Entity|null $target
     * @param Variable[] $variables
     * @param FlowItemExecutor|null $parent
     * @param Event|null $event
     * @param \Closure|null $onComplete
     * @param \Closure|null $onError
     * @param Recipe|null $sourceRecipe
     */
    public function __construct(
        private array     $items,
        private ?Entity   $target,
        array             $variables = [],
        private ?self     $parent = null,
        private ?Event    $event = null,
        private ?\Closure $onComplete = null,
        private ?\Closure $onError = null,
        private ?Recipe   $sourceRecipe = null
    ) {
        if ($event === null and $parent !== null) {
            $this->event = $parent->getEvent();
        }

        $this->variableRegistry = new VariableRegistry($variables);
    }

    public function getGenerator(): \Generator {
        $maxIndex = count($this->items) - 1;
        $this->currentIndex = 0;

        while ($this->currentIndex <= $maxIndex) {
            $this->currentFlowItem = $this->items[$this->currentIndex];
            $this->lastResult = yield from $this->currentFlowItem->execute($this);
            $this->currentIndex++;
        }
    }

    public function restart(): void {
        if ($this->parent === null) {
            $this->currentIndex = -1;
        } else {
            $this->currentIndex = count($this->items);
            $this->parent->restart();
        }
    }

    public function execute(): bool {
        Await::f2c(function () {
            try {
                yield from $this->getGenerator();
            } catch (InvalidFlowValueException $e) {
                Logger::warning(Language::get("action.error", [$e->getFlowItemName(), $e->getMessage()]), $this->target);
                if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
            } catch (MineflowException $e) {
                if (!($e instanceof RecipeInterruptException)) {
                    if (!empty($e->getMessage())) Logger::warning($e->getMessage(), $this->target);
                    if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
                }
            }

            if ($this->onComplete !== null) ($this->onComplete)($this);
        });
        return true;
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
        return Mineflow::getVariableHelper()->replaceVariables($text, $this->getVariables());
    }

    public function getVariable(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);

        $variable = $this->variableRegistry->get($name) ?? ($this->parent?->getVariable($name));

        if ($variable === null) return null;

        foreach ($names as $name1) {
            if (!($variable instanceof IteratorVariable) and !($variable instanceof ObjectVariable)) return null;
            $variable = $variable->getProperty($name1);
        }
        return $variable;
    }

    public function getVariables(): array {
        $variables = $this->variableRegistry->getAll();
        if ($this->parent !== null) {
            $variables = array_merge($this->parent->getVariables(), $variables);
        }
        return $variables;
    }

    public function getVariableRegistryCopy(): VariableRegistry {
        return new VariableRegistry($this->getVariables());
    }

    public function addVariable(string $name, Variable $variable, bool $onlyThisScope = false): void {
        $this->variableRegistry->add($name, $variable);

        if (!$onlyThisScope and $this->parent !== null) {
            $this->parent->addVariable($name, $variable);
        }
    }

    public function removeVariable(string $name): void {
        $this->variableRegistry->remove($name);
        $this->parent?->removeVariable($name);
    }

    public function getRootExecutor(): FlowItemExecutor {
        return $this->parent?->getRootExecutor() ?? $this;
    }
}