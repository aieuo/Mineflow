<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use function array_pop;
use function floor;
use function implode;

class RegisterCraftingRecipe extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    public function __construct(string $i1 = "", string $i2 = "", string $i3 = "", string $i4 = "", string $i5 = "", string $i6 = "", string $i7 = "", string $i8 = "", string $i9 = "", string $o = "") {
        parent::__construct(self::REGISTER_SHAPED_RECIPE, FlowItemCategory::ITEM);

        $this->setInputItemVariableNames([$i1, $i2, $i3, $i4, $i5, $i6, $i7, $i8, $i9]);
        $this->setItemVariableName($o, "output");
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
            $input = $this->getItemVariableName("input".$i);
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
        $details[] = "- ".$this->getItemVariableName("output");
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName("output") !== "";
    }

    public function setInputItemVariableNames(array $items): void {
        for ($i = 0; $i < 9; $i++) {
            $this->setItemVariableName($items[$i], "input".$i);
        }
    }

    public function getInputItemVariableNames(): array {
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            $items[] = $this->getItemVariableName("input".$i);
        }
        return $items;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $output = $this->getItem($source, "output");
        $shape = ["", "", ""];
        $ingredients = [];
        $keys = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
        $index = -1;
        $items = [];
        for ($i = 0; $i < 9; $i++) {
            try {
                $input = $this->getItem($source, "input".$i);
                $itemId = $input->getId().":".$input->getMeta();
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
            new ItemVariableDropdown($variables, $this->getItemVariableName("input0"), "@action.registerShapedRecipe.ingredients A", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input1"), "@action.registerShapedRecipe.ingredients B", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input2"), "@action.registerShapedRecipe.ingredients C", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input3"), "@action.registerShapedRecipe.ingredients D", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input4"), "@action.registerShapedRecipe.ingredients E", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input5"), "@action.registerShapedRecipe.ingredients F", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input6"), "@action.registerShapedRecipe.ingredients G", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input7"), "@action.registerShapedRecipe.ingredients H", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("input8"), "@action.registerShapedRecipe.ingredients I", true),
            new ItemVariableDropdown($variables, $this->getItemVariableName("output"), "@action.registerShapedRecipe.results RESULT"),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocess(function (array $data) {
                $result = array_pop($data);
                return [$data, $result];
            });
        });
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
