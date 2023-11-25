<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Language;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use function array_pop;
use function floor;
use function implode;

class RegisterCraftingRecipe extends FlowItem {

    private ItemArgument $output;
    /** @var ItemArgument[] */
    private array $ingredients;

    /**
     * @param string[] $ingredients
     * @param string $output
     */
    public function __construct(array $ingredients = [], string $output = "") {
        parent::__construct(self::REGISTER_SHAPED_RECIPE, FlowItemCategory::ITEM);

        $characters = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $this->ingredients = [];
        for ($i = 0; $i < 9; $i ++) {
            $this->ingredients[] = new ItemArgument(
                "input".$i,
                $ingredients[$i] ?? "",
                "@action.registerShapedRecipe.ingredients ".$characters[$i],
                true
            );
        }
        $this->output = new ItemArgument("output", $output, "@action.registerShapedRecipe.results RESULT");
    }

    public function getName(): string {
        return Language::get("action.registerShapedRecipe.name");
    }

    public function getDescription(): string {
        return Language::get("action.registerShapedRecipe.detail");
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();

        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            $input = (string)$this->ingredients[$i];
            if ($input !== "") {
                if (isset($items[$input])) {
                    $key = $keys[$items[$input]];
                } else {
                    $index++;
                    $items[$input] = $index;
                    $key = $keys[$index];
                    $ingredients[$key] = $input;
                }
            } else {
                $key = " ";
            }
            $shape[floor($i / 3)] .= $key;
        }
        $shape = $this->trimShape($shape);

        $details = ["---".Language::get("action.registerShapedRecipe.detail")."---"];
        $details[] = Language::get("action.registerShapedRecipe.shape");
        foreach ($shape as $line) {
            $details[] = "- |".$line."|";
        }
        $details[] = Language::get("action.registerShapedRecipe.ingredients");
        foreach ($ingredients as $key => $ingredient) {
            $details[] = "- ".$key." = ".$ingredient;
        }
        $details[] = Language::get("action.registerShapedRecipe.results");
        $details[] = "- ".$this->output;
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function isDataValid(): bool {
        return $this->output->isValid();
    }

    public function getIngredients(): array {
        return $this->ingredients;
    }

    public function getOutput(): ItemArgument {
        return $this->output;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $output = $this->output->getItem($source);
        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            try {
                $input = $this->ingredients[$i]->getItem($source);
                $itemId = $input->getStateId();
                if (isset($items[$itemId])) {
                    $key = $keys[$items[$itemId]];
                } else {
                    $index++;
                    $items[$itemId] = $index;
                    $key = $keys[$index];
                    $ingredients[$key] = $input;
                }
            } catch (InvalidFlowValueException) {
                $key = " ";
            }
            $shape[floor($i / 3)] .= $key;
        }

        $shape = $this->trimShape($shape);
        if (empty($shape)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.registerShapedRecipe.recipe.empty"));
        }

        $recipe = new ShapedRecipe($shape, $ingredients, [$output]);
        Server::getInstance()->getCraftingManager()->registerShapedRecipe($recipe);

        yield Await::ALL;
    }

    public function trimShape(array $shape): array {
        $col = ["", "", ""];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $col[$i] .= $shape[$j][$i];
            }
        }

        $colStart = 0;
        $colEnd = 2;
        if ($col[0] === "   ") $colStart++;
        if ($col[2] === "   ") $colEnd--;
        if ($col[0] === "   " and $col[2] !== "   " and $col[1] === "   ") $colStart++;
        if ($col[2] === "   " and $col[0] !== "   " and $col[1] === "   ") $colEnd--;
        if ($col[0] === "   " and $col[1] === "   " and $col[2] === "   ") return [];

        for ($i = 0; $i < 3; $i++) {
            $line = substr($shape[$i], $colStart, $colEnd - $colStart + 1);
            $shape[$i] = $line;
            if (trim($line) === "") unset($shape[$i]);
        }
        return array_values($shape);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->ingredients[0]->createFormElement($variables),
            $this->ingredients[1]->createFormElement($variables),
            $this->ingredients[2]->createFormElement($variables),
            $this->ingredients[3]->createFormElement($variables),
            $this->ingredients[4]->createFormElement($variables),
            $this->ingredients[5]->createFormElement($variables),
            $this->ingredients[6]->createFormElement($variables),
            $this->ingredients[7]->createFormElement($variables),
            $this->ingredients[8]->createFormElement($variables),
            $this->output->createFormElement($variables),
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->preprocess(function (array $data) {
                $result = array_pop($data);
                return [$data, $result];
            });
        });
    }

    public function loadSaveData(array $content): void {
        for ($i = 0; $i < 9; $i++) {
            $this->ingredients[$i]->value($content[0][$i] ?? "");
        }
        $this->output->value($content[1]);
    }

    public function serializeContents(): array {
        return [$this->ingredients, $this->output];
    }

    public function __clone(): void {
        $ingredients = [];
        foreach ($this->ingredients as $ingredient) {
            $ingredients[] = clone $ingredient;
        }
        $this->ingredients = $ingredients;
        $this->output = clone $this->output;
    }
}
