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
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\parser\exception\VariableParseException;
use aieuo\mineflow\variable\parser\node\BinaryExpressionNode;
use aieuo\mineflow\variable\parser\node\GlobalMethodNode;
use aieuo\mineflow\variable\parser\node\IdentifierNode;
use aieuo\mineflow\variable\parser\node\MethodNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\PropertyNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use aieuo\mineflow\variable\parser\VariableLexer;
use aieuo\mineflow\variable\parser\VariableParser;
use function array_key_last;
use function array_pop;
use function array_search;
use function explode;
use function is_string;
use function preg_replace;
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
                try {
                    $tokens = (new VariableLexer($variable))->lexer();
                    $ast = (new VariableParser($tokens))->parse();
                } catch (VariableParseException $e) {
                    Main::getInstance()->getLogger()->error("Failed to parse variable during upgrading recipe: ".$e->getMessage()."; ".$variable);
                    continue;
                }

                $data = $this->removeDirectActionCallNested($data, $variable, $ast, $item, $parents);
            }

            $newContents[] = $data;
        }
        $item->loadSaveData($newContents);
        array_pop($parents);
    }

    private function removeDirectActionCallNested(string $data, string $variable, Node $node, FlowItem $item, array $parents): string {
        if ($node instanceof GlobalMethodNode) {
            $newAction = $this->extractDirectActionCall($node, $resultName);

            if ($newAction !== null) {
                $this->insertActionBefore($item, $newAction, $parents);

                if ($newAction->getReturnValueType() === FlowItem::RETURN_VARIABLE_VALUE) {
                    $data = str_replace("{".$variable."}", "{".$resultName."}", $data);
                } else {
                    $data = str_replace("{".$variable."}", $resultName, $data);
                }
            }
        }

        if ($node instanceof WrappedNode) {
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getStatement(), $item, $parents);
        }
        if ($node instanceof BinaryExpressionNode) {
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getLeft(), $item, $parents);
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getRight(), $item, $parents);
        }
        if ($node instanceof UnaryExpressionNode) {
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getRight(), $item, $parents);
        }
        if ($node instanceof PropertyNode) {
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getLeft(), $item, $parents);
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getIdentifier(), $item, $parents);
        }
        if ($node instanceof MethodNode) {
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getLeft(), $item, $parents);
            $data = $this->removeDirectActionCallNested($data, $variable, $node->getIdentifier(), $item, $parents);
            foreach ($node->getArguments() as $arg) {
                $data = $this->removeDirectActionCallNested($data, $variable, $arg, $item, $parents);
            }
        }

        return $data;
    }

    private function extractDirectActionCall(GlobalMethodNode $node, string &$resultName = null): ?FlowItem {
        $identifier = $node->getIdentifier();
        if (!($identifier instanceof IdentifierNode)) return null;

        $name = $identifier->getName();
        $args = [];
        foreach ($node->getArguments() as $argument) {
            if (!($argument instanceof IdentifierNode)) return null;
            $args[] = $argument->getName();
        }

        switch ($name) {
            case FlowItemIds::CREATE_ITEM_VARIABLE:
                $resultName = $args[3] ?? "item";
                return new CreateItemVariable($args[0] ?? "", (int)($args[1] ?? 0), $args[2] ?? "", $resultName);
            case FlowItemIds::CREATE_BLOCK_VARIABLE:
                $resultName = $args[1] ?? "block";
                return new CreateBlockVariable($args[0] ?? "", $resultName);
            case FlowItemIds::CREATE_POSITION_VARIABLE:
                $resultName = $args[4] ?? "pos";
                return new CreatePositionVariable((float)($args[0] ?? 0), (float)($args[1] ?? 0), (float)($args[2] ?? 0), $args[3] ?? "", $resultName);
            case FlowItemIds::FOUR_ARITHMETIC_OPERATIONS:
                $resultName = $args[3] ?? "result";
                return new FourArithmeticOperations((float)($args[0] ?? 0), (int)($args[1] ?? 0), (float)($args[2] ?? 0), $resultName);
        }

        return null;
    }

    private function insertActionBefore(FlowItem $item, FlowItem $action, array $parents): void {
        $container = array_pop($parents);
        if (!($container instanceof Recipe)) {
            $container = array_pop($parents);
        }

        $index = array_search($item, $container->getItems(), true);
        $container->pushItem($index === false ? 0 : $index, $action);
    }

    private function replacePositionVariable(FlowItem $item): void {
        foreach ($item->getArguments() as $argument) {
            if ($argument instanceof PositionArgument) {
                $variable = preg_replace("/^(target|entity|player)\d*$/u", "$0.location", (string)$argument->getVariableName());
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
