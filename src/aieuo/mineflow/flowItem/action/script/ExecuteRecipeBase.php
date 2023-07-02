<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use function array_map;
use function explode;
use function implode;
use function substr;

abstract class ExecuteRecipeBase extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    /** @var string[] */
    private array $args;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::SCRIPT,
        private string $recipeName = "",
        string         $args = ""
    ) {
        parent::__construct($id, $category, [FlowItemPermission::LOOP]);

        $this->args = array_filter(array_map("trim", explode(",", $args)), fn(string $t) => $t !== "");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getRecipeName()];
    }

    public function setRecipeName(string $name): void {
        $this->recipeName = $name;
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

    public function getRecipe(FlowItemExecutor $source): Recipe {
        $name = $source->replaceVariables($this->getRecipeName());

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

    public function getArguments(FlowItemExecutor $source): array {
        $helper = Mineflow::getVariableHelper();
        $args = [];
        foreach ($this->getArgs() as $arg) {
            $name = $helper->isSimpleVariableString($arg) ? substr($arg, 1, -1) : $arg;
            $args[$name] = $helper->copyOrCreateVariable($arg, $source);
        }
        return $args;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->page(0, function (SimpleEditFormBuilder $page) {
            $page->elements([
                new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            ])->response(function (EditFormResponseProcessor $response) {
                $response->setLoader(fn(array $data) => $this->setRecipeName($data[0]));
            });
        });

        $builder->page(1, function (SimpleEditFormBuilder $page) use($variables) {
            $recipeManager = Mineflow::getRecipeManager();
            $recipe = $recipeManager->get(...$recipeManager->parseName($this->getRecipeName()));

            if ($recipe === null) {
                $page->elements([
                    new ExampleInput("@action.callRecipe.form.args", "{target}, 1, aieuo", implode(", ", $this->getArgs()), false),
                ])->response(function (EditFormResponseProcessor $response) {
                    $response->preprocessAt(0, fn($value) => array_map("trim", explode(",", $value)));
                });
            } else {
                $args = $this->getArgs();
                $elements = [];
                $isObjectVariable = [];
                foreach ($recipe->getArguments() as $i => $argument) {
                    $elements[] = $argument->getInputElement($variables, $args[$i] ?? null);
                    $isObjectVariable[] = $argument->getDummyVariable()->isObjectVariableType();
                }

                $page->elements($elements)->response(function (EditFormResponseProcessor $response) use($isObjectVariable) {
                    $response->preprocess(function ($data) use($isObjectVariable) {
                        $args = [];
                        foreach ($data as $i => $arg) {
                            if ($isObjectVariable[$i]) {
                                $args[] = "{".$arg."}";
                            } else {
                                $args[] = $arg;
                            }
                        }
                        return [$args];
                    });
                });
            }

            $page->response(function (EditFormResponseProcessor $response) {
                $response->unshift($this->getRecipeName());
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->setRecipeName($content[0]);
        $this->setArgs($content[1] ?? []);
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getArgs()];
    }
}
