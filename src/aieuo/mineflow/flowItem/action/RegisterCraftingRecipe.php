<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\Server;

class RegisterCraftingRecipe extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::REGISTER_SHAPED_RECIPE;

    protected $name = "action.registerRecipe.name";
    protected $detail = "action.registerRecipe.detail";
    protected $detailDefaultReplace = ["inputs", "outputs"];

    protected $category = Category::ITEM;

    public function __construct(string $i1 = "", string $i2 = "", string $i3 = "", string $i4 = "", string $i5 = "", string $i6 = "", string $i7 = "", string $i8 = "", string $i9 = "", string $o = "") {
        $this->setInputItemVariableNames([$i1, $i2, $i3, $i4, $i5, $i6, $i7, $i8, $i9]);
        $this->setItemVariableName($o, "output");
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName("output") !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();

        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i=0; $i<9; $i++) {
            $input = $this->getItemVariableName("input".$i);
            if ($input !== "") {
                if (isset($items[$input])) {
                    $key = $keys[$items[$input]];
                } else {
                    $index ++;
                    $items[$input] = $index;
                    $key = $keys[$index];
                    $ingredients[$key] = $input;
                }
            } else {
                $key = " ";
            }
            $shape[floor($i/3)] .= $key;
        }
        $shape = $this->trimShape($shape);

        $details = ["---".Language::get($this->detail)."---"];
        $details[] = Language::get("action.registerRecipe.shape");
        foreach ($shape as $line) {
            $details[] = "- |".$line."|";
        }
        $details[] = Language::get("action.registerRecipe.ingredients");
        foreach ($ingredients as $key => $ingredient) {
            $details[] = "- ".$key." = ".$ingredient;
        }
        $details[] = Language::get("action.registerRecipe.results");
        $details[] = "- ".$this->getItemVariableName("output");
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function setInputItemVariableNames(array $items): void {
        for ($i=0; $i<9; $i++) {
            $this->setItemVariableName($items[$i], "input".$i);
        }
    }

    public function getInputItemVariableNames(): array {
        $items = [];
        for ($i=0; $i<9; $i++) {
            $items[] = $this->getItemVariableName("input".$i);
        }
        return $items;
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $output = $this->getItem($origin, "output");
        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i=0; $i<9; $i++) {
            try {
                $input = $this->getItem($origin, "input".$i);
                $itemId = $input->getId().":".$input->getDamage();
                if (isset($items[$itemId])) {
                    $key = $keys[$items[$itemId]];
                } else {
                    $index ++;
                    $items[$itemId] = $index;
                    $key = $keys[$index];
                    $ingredients[$key] = $input;
                }
            } catch (InvalidFlowValueException $e) {
                $key = " ";
            }
            $shape[floor($i/3)] .= $key;
        }

        $shape = $this->trimShape($shape);
        if (empty($shape)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.registerRecipe.recipe.empty"));
        }

        $recipe = new ShapedRecipe($shape, $ingredients, [$output]);
        Server::getInstance()->getCraftingManager()->registerShapedRecipe($recipe);
        yield true;
    }

    public function trimShape(array $shape): array {
        $col = ["", "", ""];
        for($i = 0; $i < 3; $i ++) {
            for($j = 0; $j < 3; $j ++) {
                $col[$i] .= $shape[$j][$i];
            }
        }

        $colStart = 0;
        $colEnd = 2;
        if ($col[0] === "   ") $colStart ++;
        if ($col[2] === "   ") $colEnd --;
        if ($col[0] === "   " and $col[2] !== "   " and $col[1] === "   ") $colStart ++;
        if ($col[2] === "   " and $col[0] !== "   " and $col[1] === "   ") $colEnd --;
        if ($col[0] === "   " and $col[1] === "   " and $col[2] === "   ") return [];

        for($i = 0; $i < 3; $i ++) {
            $line = substr($shape[$i], $colStart, $colEnd - $colStart + 1);
            $shape[$i] = $line;
            if (trim($line) === "") unset($shape[$i]);
        }
        return array_values($shape);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName("input0"), "@action.registerRecipe.ingredients A", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input1"), "@action.registerRecipe.ingredients B", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input2"), "@action.registerRecipe.ingredients C", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input3"), "@action.registerRecipe.ingredients D", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input4"), "@action.registerRecipe.ingredients E", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input5"), "@action.registerRecipe.ingredients F", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input6"), "@action.registerRecipe.ingredients G", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input7"), "@action.registerRecipe.ingredients H", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input8"), "@action.registerRecipe.ingredients I", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("output"), "@action.registerRecipe.results RESULT"),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setInputItemVariableNames($content[0]);
        $this->setItemVariableName($content[1], "output");
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getInputItemVariableNames(), $this->getItemVariableName("output")];
    }
}