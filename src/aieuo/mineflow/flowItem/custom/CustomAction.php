<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\custom;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\argument\RecipeArgument;
use aieuo\mineflow\recipe\Recipe;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function str_replace;
use function substr;

class CustomAction extends FlowItem {
    use HasSimpleEditForm;

    private array $isObjectVariable = [];

    public function __construct(
        private string $addonName,
        string         $id,
        string         $category,
        private string $actionName,
        private string $actionDescription,
        private Recipe $recipe,
        private array  $arguments = [],
    ) {
        parent::__construct($id, $category);

        foreach ($this->recipe->getArguments() as $i => $argument) {
            $this->isObjectVariable[$i] = $argument->getDummyVariable()->isObjectVariableType();
        }
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
        return array_map(fn(RecipeArgument $arg) => $arg->getName(), $this->getRecipe()->getArguments());
    }

    public function getDetailReplaces(): array {
        return $this->arguments;
    }

    public function getAddonName(): string {
        return $this->addonName;
    }

    public function getRecipe(): Recipe {
        return $this->recipe;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function setArguments(array $arguments): void {
        $this->arguments = $arguments;
    }

    public function isDataValid(): bool {
        return true;
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
        foreach ($this->getArguments() as $i => $arg) {
            if ($this->isObjectVariable[$i]) {
                $args[$arg] = $executor->getVariable($executor->replaceVariables($arg));
            } else {
                $name = $helper->isSimpleVariableString($arg) ? substr($arg, 1, -1) : $arg;
                $args[$name] = $helper->copyOrCreateVariable($arg, $executor);
            }
        }
        return $args;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $arguments = $this->getRecipe()->getArguments();
        $elements = [];
        foreach ($arguments as $i => $argument) {
            $elements[] = $argument->getInputElement($variables, $this->getArguments()[$i] ?? null);
        }
        $builder->elements($elements);
    }

    public function loadSaveData(array $content): void {
        $this->setArguments($content);
    }

    public function serializeContents(): array {
        return $this->getArguments();
    }
}
