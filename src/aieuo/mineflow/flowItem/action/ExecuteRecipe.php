<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ExecuteRecipe extends FlowItem {

    protected $id = self::EXECUTE_RECIPE;

    protected $name = "action.executeRecipe.name";
    protected $detail = "action.executeRecipe.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $recipeName;

    /** @var string[] */
    private $args;

    public function __construct(string $name = "", string $args = "") {
        $this->recipeName = $name;
        $this->args = array_filter(array_map("trim", explode(",", $args)), function (string $t) {
            return $t !== "";
        });
    }

    public function setRecipeName(string $name): self {
        $this->recipeName = $name;
        return $this;
    }

    public function getRecipeName(): string {
        return $this->recipeName;
    }

    public function setArgs(array $args): void {
        $this->args = $args;
    }

    public function getArgs(): array {
        return $this->args;
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getRecipeName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);
        $args = $this->getArguments($source);

        $recipe->executeAllTargets($source->getTarget(), $source->getVariables(), $source->getEvent(), $args);
        yield true;
    }

    public function getRecipe(FlowItemExecutor $source): Recipe {
        $name = $source->replaceVariables($this->getRecipeName());

        $recipeManager = Main::getRecipeManager();
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

    public function getArguments(FlowItemExecutor $source): array {
        $helper = Main::getVariableHelper();
        $args = [];
        foreach ($this->getArgs() as $arg) {
            if (!$helper->isVariableString($arg)) {
                $args[] = $helper->replaceVariables($arg, $source->getVariables());
                continue;
            }
            $arg = $source->getVariable(substr($arg, 1, -1)) ?? $helper->get(substr($arg, 1, -1)) ?? $arg;
            $args[] = $arg;
        }
        return $args;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new ExampleInput("@action.callRecipe.form.args", "{target}, 1, aieuo", implode(", ", $this->getArgs()), false),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0], array_map("trim", explode(",", $data[1]))];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setRecipeName($content[0]);
        $this->setArgs($content[1] ?? []);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getArgs()];
    }
}