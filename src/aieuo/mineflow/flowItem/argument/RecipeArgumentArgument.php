<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\Variable;
use function array_map;
use function count;
use function explode;
use function substr;

class RecipeArgumentArgument extends StringArrayArgument {

    private bool $isSeparatedInput = true;
    /** @var bool[] */
    private array $objectVariableFlags = [];

    /**
     * @param string $name
     * @param array $value
     * @param string $description
     * @param string $example
     * @param (\Closure(): Recipe)|null $recipeName
     */
    public function __construct(
        string            $name,
        array             $value = [],
        string            $description = "@action.callRecipe.form.args",
        string            $example = "{target}, 1, aieuo",
        private ?\Closure $recipeName = null,
    ) {
        parent::__construct($name, $value, $description, $example, true, ",");
    }

    /**
     * @param \Closure(): Recipe $recipeName
     * @return $this
     */
    public function recipeName(\Closure $recipeName): self {
        $this->recipeName = $recipeName;
        return $this;
    }

    /**
     * @param FlowItemExecutor $executor
     * @return array<string, Variable>
     */
    public function getVariableArray(FlowItemExecutor $executor): array {
        $helper = Mineflow::getVariableHelper();
        $args = [];
        foreach ($this->getRawArray() as $arg) {
            $name = $helper->isSimpleVariableString($arg) ? substr($arg, 1, -1) : $arg;
            $args[$name] = $helper->copyOrCreateVariable($arg, $executor->getVariableRegistryCopy());
        }
        return $args;
    }

    public function createFormElements(array $variables): array {
        $recipeManager = Mineflow::getRecipeManager();
        $recipe = $recipeManager->get(...$recipeManager->parseName(($this->recipeName)()));

        $this->isSeparatedInput = true;

        if ($recipe === null or count($recipe->getArguments()) === 0) {
            $this->isSeparatedInput = false;
            return [new ExampleInput($this->getDescription(), $this->getExample(), $this->getRawString())];
        }

        $args = $this->getRawArray();
        $elements = [];
        $this->objectVariableFlags = [];
        foreach ($recipe->getArguments() as $i => $argument) {
            $elements[] = $argument->getInputElement($variables, $args[$i] ?? null);
            $this->objectVariableFlags[] = $argument->getDummyVariable()->isObjectVariableType();
        }

        return $elements;
    }

    /**
     * @param array{0: string}|string[] $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        if (!$this->isSeparatedInput) {
            $this->value(array_map("trim", explode(",", $data[0])));
        } else {
            $args = [];
            foreach ($data as $i => $arg) {
                if ($this->objectVariableFlags[$i]) {
                    $args[] = "{".$arg."}";
                } else {
                    $args[] = $arg;
                }
            }
            $this->value($args);
        }
    }

    public function __clone(): void {
        parent::__clone();
        
        $this->recipeName = null;
    }
}