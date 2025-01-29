<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\RecipeArgumentArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\CustomFormFlowItemEditor;
use aieuo\mineflow\flowItem\editor\MultiplePageFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;

abstract class ExecuteRecipeBase extends SimpleAction {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::SCRIPT,
        string $recipeName = "",
        array $args = [],
    ) {
        parent::__construct($id, $category, [FlowItemPermission::LOOP]);

        $this->setArguments([
            StringArgument::create("name", $recipeName, "@action.executeRecipe.form.name")->example("aieuo"),
            RecipeArgumentArgument::create("args", $args)->recipeName(fn() => $this->getRecipeName()->getRawString()),
        ]);
    }

    public function getRecipeName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getArgs(): RecipeArgumentArgument {
        return $this->getArgument("args");
    }

    public function getRecipe(FlowItemExecutor $source): Recipe {
        $name = $this->getRecipeName()->getString($source);

        $recipeManager = Mineflow::getRecipeManager();
        [$recipeName, $group] = $recipeManager->parseName($name);
        if (empty($group)) {
            $sr = $source->getSourceRecipe();
            if ($sr !== null) $group = $sr->getGroup();
        }

        $recipe = $recipeManager->get($recipeName, $group) ?? $recipeManager->get($recipeName, "");
        if ($recipe === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.executeRecipe.notFound"));
        }

        return $recipe;
    }

    public function getEditors(): array {
        $arguments = [];
        $recipeArgumentArgument = $this->getArgs();

        foreach ($this->getArguments() as $argument) {
            if (!($argument instanceof CustomFormEditorArgument) or $argument === $recipeArgumentArgument) {
                continue;
            }

            $arguments[] = $argument;
        }

        return [
            new MultiplePageFlowItemEditor([
                new CustomFormFlowItemEditor($this, $arguments),
                new CustomFormFlowItemEditor($this, [$recipeArgumentArgument]),
            ], primary: true),
        ];
    }

    public function __clone(): void {
        parent::__clone();

        $this->getArgs()->recipeName(fn() => $this->getRecipeName()->getRawString());
    }
}