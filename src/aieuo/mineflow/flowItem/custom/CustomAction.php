<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\custom;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;
use function array_map;
use function str_replace;
use function substr;

class CustomAction extends FlowItem {

    private array $isObjectVariable = [];

    public function __construct(
        private readonly string $addonName,
        string                  $id,
        string                  $category,
        private readonly string $actionName,
        private readonly string $actionDescription,
        private readonly Recipe $recipe,
    ) {
        parent::__construct($id, $category);

        $arguments = [];
        foreach ($this->recipe->getArguments() as $i => $argument) {
            $arguments[$i] = $argument->toFlowItemArgument();
            $this->isObjectVariable[$i] = $argument->getDummyVariable()->isObjectVariableType();
        }
        $this->setArguments($arguments);
    }

    public function getName(): string {
        return $this->actionName;
    }

    public function getDescription(): string {
        $description = $this->actionDescription;
        $replaces = array_map(fn($replace) => "§7<".$replace.">§f", $this->getDetailDefaultReplaces());
        foreach ($replaces as $cnt => $value) {
            $description = str_replace("{%".$cnt."}", $value, $description);
        }
        return $description;
    }

    public function getDetail(): string {
        $detail = $this->actionDescription;
        foreach ($this->getDetailReplaces() as $cnt => $value) {
            $detail = str_replace("{%".$cnt."}", $value, $detail);
        }
        return $detail;
    }

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => $value->getName(), $this->getArguments());
    }

    public function getDetailReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => (string)$value, $this->getArguments());
    }

    public function getAddonName(): string {
        return $this->addonName;
    }

    public function getRecipe(): Recipe {
        return $this->recipe;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe();
        $args = $this->getArgumentVariables($source);

        yield from Await::promise(fn($resolve) => $recipe->executeAllTargets(
            $source->getTarget(), $source->getVariables(), $source->getEvent(), $args, $source, $resolve
        ));
    }

    public function getArgumentVariables(FlowItemExecutor $executor): array {
        $helper = Mineflow::getVariableHelper();
        $args = [];
        foreach ($this->getArguments() as $i => $argument) {
            $arg = (string)$argument;
            if ($this->isObjectVariable[$i]) {
                $args[$arg] = $executor->getVariable($executor->replaceVariables($arg));
            } else {
                $name = $helper->isSimpleVariableString($arg) ? substr($arg, 1, -1) : $arg;
                $args[$name] = $helper->copyOrCreateVariable($arg, $executor->getVariableRegistryCopy());
            }
        }
        return $args;
    }
}