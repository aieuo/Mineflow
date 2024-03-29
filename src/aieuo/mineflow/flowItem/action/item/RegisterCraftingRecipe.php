<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\ItemFixedArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use function floor;
use function implode;

class RegisterCraftingRecipe extends SimpleAction {

    /**
     * @param string[] $ingredients
     * @param string $output
     */
    public function __construct(array $ingredients = [], string $output = "") {
        parent::__construct(self::REGISTER_SHAPED_RECIPE, FlowItemCategory::ITEM);

        $characters = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $this->setArguments([
            ItemFixedArrayArgument::create("ingredients", $ingredients, "@action.registerShapedRecipe.ingredients")
                ->format(fn(int $i, string $desc) => $desc." ".$characters[$i])
                ->count(9)->optional(),
            ItemArgument::create("output", $output, "@action.registerShapedRecipe.results RESULT"),
        ]);
    }

    public function getDetail(): string {
        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            $input = $this->getIngredients()->getVariableNameAt($i);
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
        $details[] = "- ".$this->getOutput();
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function getIngredients(): ItemFixedArrayArgument {
        return $this->getArgument("ingredients");
    }

    public function getOutput(): ItemArgument {
        return $this->getArgument("output");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $output = $this->getOutput()->getItem($source);
        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            try {
                $input = $this->getIngredients()->getItemAt($source, $i);
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
}
