<?php
declare(strict_types=1);


namespace aieuo\mineflow\recipe;

use aieuo\mineflow\flowItem\action\block\CreateBlockVariable;
use aieuo\mineflow\flowItem\action\item\CreateItemVariable;
use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;
use aieuo\mineflow\flowItem\action\world\CreatePositionVariable;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\argument\FlowItemArrayArgument;
use aieuo\mineflow\flowItem\argument\ObjectVariableArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\Mineflow;
use function array_key_last;
use function array_map;
use function array_pop;
use function array_search;
use function explode;
use function is_array;
use function is_string;
use function preg_replace;
use function str_contains;
use function str_replace;
use function version_compare;

class RecipeUpgrader {
    public function needUpgrade(string $from, string $current, string $target): bool {
        return version_compare($target, $current, "<=") and version_compare($from, $target, "<");
    }

    public function upgradeBeforeLoad(array $recipeData): array {
        $createdVersion = $recipeData["plugin_version"] ?? "0";
        $currentVersion = Mineflow::getPluginVersion();

        if (version_compare($createdVersion, $currentVersion, "=")) return $recipeData;

        if ($this->needUpgrade($createdVersion, $currentVersion, "2.6.0")) {
            $newTriggers = [];
            foreach ($recipeData["triggers"] ?? [] as $trigger) {
                if ($trigger["type"] === "event") {
                    $tmp = explode("\\", str_replace("/", "\\", $trigger["key"]));
                    $trigger["key"] = $tmp[array_key_last($tmp)];
                }
                $newTriggers[] = $trigger;
            }
            $recipeData["triggers"] = $newTriggers;
        }

        return $recipeData;
    }

    public function upgradeAfterLoad(Recipe $recipe): void {
        $createdVersion = $recipe->getPluginVersion();
        $currentVersion = Mineflow::getPluginVersion();

        if (version_compare($createdVersion, $currentVersion, "=")) return;

        if ($this->needUpgrade($createdVersion, $currentVersion, "2.0.0")) {
            $oldToNewTargetMap = [
                4 => Recipe::TARGET_NONE,
                0 => Recipe::TARGET_DEFAULT,
                1 => Recipe::TARGET_SPECIFIED,
                2 => Recipe::TARGET_BROADCAST,
                3 => Recipe::TARGET_RANDOM,
            ];
            if (isset($oldToNewTargetMap[$recipe->getTargetType()])) {
                $recipe->setTargetSetting($oldToNewTargetMap[$recipe->getTargetType()], $recipe->getTargetOptions());
            }

            foreach ($recipe->getActionsFlatten() as $action) {
                $this->replaceLevelToWorld($action);
            }

            $recipe->setPluginVersion("2.0.0");
        }

        if ($this->needUpgrade($recipe->getPluginVersion(), $currentVersion, "2.6.0")) {
            $recipe->setPluginVersion("2.6.0");
        }

        if ($this->needUpgrade($recipe->getPluginVersion(), $currentVersion, "3.0.0")) {
            foreach ($recipe->getActions() as $action) {
                $this->removeDirectActionCall($action, [$recipe]);
            }

            foreach ($recipe->getActionsFlatten() as $action) {
                $this->replacePositionVariable($action);
                $this->replaceMapOperator($action);
            }

            $recipe->setPluginVersion("3.0.0");
        }
    }

    private function replaceLevelToWorld(FlowItem $action): void {
        foreach ($action->getArguments() as $argument) {
            $this->processArgumentStrings($argument, function (string $value) {
                $value = str_replace(["origin_level", "target_level"], ["origin_world", "target_world"], $value);
                return preg_replace("/({.+\.)level((\.?.+)*})/u", "$1world$2", $value);
            });
        }
    }

    private function removeDirectActionCall(FlowItem $item, array $parents): void {
        $variableHelper = Mineflow::getVariableHelper();

        foreach ($item->getArguments() as $argument) {
            if ($argument instanceof FlowItemContainer) {
                $parents[] = $argument;
                foreach ($argument->getItems() as $action) {
                    $this->removeDirectActionCall($action, $parents);
                }
                return;
            }
        }

        $newContents = [];
        foreach ($item->serializeContents() as $data) {
            if (!is_string($data)) {
                $newContents[] = $data;
                continue;
            }

            $variables = $variableHelper->findVariables($data);
            foreach ($variables as $variable) {
                $tokens = $variableHelper->lexer($variable);
                $ast = $variableHelper->parse($tokens);
                if (!is_array($ast)) continue;
                if (!isset($ast["op"]) or $ast["op"] !== "()") continue;
                if (!isset($ast["left"]) or !is_string($ast["left"])) continue;
                if (str_contains($ast["left"], ".")) continue;

                $name = $ast["left"];
                $args = array_map(fn($arg) => (string)$arg, is_array($ast["right"]) ? $ast["right"] : [$ast["right"]]);

                switch ($name) {
                    case FlowItemIds::CREATE_ITEM_VARIABLE:
                        $resultName = $args[3] ?? "item";
                        $newAction = new CreateItemVariable($args[0] ?? "", (int)($args[1] ?? 0), $args[2] ?? "", $resultName);
                        break;
                    case FlowItemIds::CREATE_BLOCK_VARIABLE:
                        $resultName = $args[1] ?? "block";
                        $newAction = new CreateBlockVariable($args[0] ?? "", $resultName);
                        break;
                    case FlowItemIds::CREATE_POSITION_VARIABLE:
                        $resultName = $args[4] ?? "pos";
                        $newAction = new CreatePositionVariable((float)($args[0] ?? 0), (float)($args[1] ?? 0), (float)($args[2] ?? 0), $args[3] ?? "", $resultName);
                        break;
                    case FlowItemIds::FOUR_ARITHMETIC_OPERATIONS:
                        $resultName = $args[3] ?? "result";
                        $newAction = new FourArithmeticOperations((float)($args[0] ?? 0), (int)($args[1] ?? 0), (float)($args[2] ?? 0), $resultName);
                        break;
                    default:
                        throw new \UnexpectedValueException("§cFailed to extract direct action call: {$name}");
                }

                $this->insertActionBefore($item, $newAction, $parents);

                if ($newAction->getReturnValueType() === FlowItem::RETURN_VARIABLE_VALUE) {
                    $data = str_replace("{".$variable."}", "{".$resultName."}", $data);
                } else {
                    $data = str_replace("{".$variable."}", $resultName, $data);
                }
            }

            $newContents[] = $data;
        }
        $item->loadSaveData($newContents);
        array_pop($parents);
    }

    private function insertActionBefore(FlowItem $item, FlowItem $action, array $parents): void {
        $container = array_pop($parents);
        $index = array_search($item, $container->getItems(), true);
        if ($index === false) {
            $container->addItem($action);
        } else {
            $container->pushItem($index, $action);
        }
    }

    private function replacePositionVariable(FlowItem $item): void {
        foreach ($item->getArguments() as $argument) {
            if ($argument instanceof PositionArgument) {
                $variable = preg_replace("/^(target|entity|player)\d*$/u", "$0.location", $argument->getVariableName());
                $argument->value($variable);
            }
        }
    }

    private function replaceMapOperator(FlowItem $action): void {
        foreach ($action->getArguments() as $argument) {
            $this->processArgumentStrings($argument, function (string $value) {
                return preg_replace("/>\s*it\./u", ".", $value);
            });
        }
    }

    /**
     * @param FlowItemArgument $arg
     * @param callable(string $value): string $processor
     * @return void
     */
    protected function processArgumentStrings(FlowItemArgument $arg, callable $processor): void {
        if ($arg instanceof StringArgument) {
            $arg->value($processor($arg->getRawString()));
        } elseif ($arg instanceof StringArrayArgument) {
            $values = [];
            foreach ($arg->getRawArray() as $item) {
                $values[] = $processor($item);
            }
            $arg->value($values);
        } elseif ($arg instanceof ObjectVariableArgument) {
            $arg->value($processor($arg->getRawVariableName()));
        } elseif ($arg instanceof FlowItemArrayArgument) {
            foreach ($arg->getItems() as $item) {
                foreach ($item->getArguments() as $argument) {
                    $this->processArgumentStrings($argument, $processor);
                }
            }
        }
    }
}
