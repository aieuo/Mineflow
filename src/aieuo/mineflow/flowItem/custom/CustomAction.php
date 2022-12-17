<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\custom;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\argument\RecipeArgument;
use aieuo\mineflow\recipe\Recipe;
use function array_map;
use function substr;

class CustomAction extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $id,
        string         $category,
        private Recipe $recipe,
        private array  $arguments = [],
    ) {
        parent::__construct($id, $category);
    }

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(RecipeArgument $arg) => $arg->getName(), $this->getRecipe()->getArguments());
    }

    public function getDetailReplaces(): array {
        return $this->arguments;
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

    public function allowDirectCall(): bool {
        return false;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe();
        $args = $this->getArgumentVariables($source);

        $recipe->executeAllTargets($source->getTarget(), $source->getVariables(), $source->getEvent(), $args, $source);
        yield false;
    }

    public function getArgumentVariables(FlowItemExecutor $executor): array {
        $helper = Mineflow::getVariableHelper();
        $args = [];
        foreach ($this->getArguments() as $arg) {
            $name = $helper->isSimpleVariableString($arg) ? substr($arg, 1, -1) : $arg;
            $args[$name] = $helper->copyOrCreateVariable($arg, $executor);
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

    public function loadSaveData(array $content): FlowItem {
        $this->setArguments($content);
        return $this;
    }

    public function serializeContents(): array {
        return $this->getArguments();
    }
}