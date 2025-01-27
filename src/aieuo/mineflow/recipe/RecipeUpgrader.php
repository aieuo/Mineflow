<?php
declare(strict_types=1);


namespace aieuo\mineflow\recipe;

use aieuo\mineflow\flowItem\action\block\CreateBlockVariable;
use aieuo\mineflow\flowItem\action\item\AddEnchantment;
use aieuo\mineflow\flowItem\action\item\CreateItemVariable;
use aieuo\mineflow\flowItem\action\item\SetItemLore;
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
use aieuo\mineflow\variable\parser\EvaluableStringLexer;
use aieuo\mineflow\variable\parser\exception\VariableParseException;
use aieuo\mineflow\variable\parser\LegacyVariableParser;
use aieuo\mineflow\variable\parser\node\BinaryExpressionNode;
use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\GlobalMethodNode;
use aieuo\mineflow\variable\parser\node\MethodNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\PropertyNode;
use aieuo\mineflow\variable\parser\node\ToStringNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use Ramsey\Uuid\Uuid;
use function array_key_last;
use function array_pop;
use function array_search;
use function explode;
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
                $this->removeDirectActionCall($action, null, [$recipe]);
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

    private function removeDirectActionCall(FlowItem $item, ?FlowItem $parentItem, array $parents): void {
        foreach ($item->getArguments() as $argument) {
            if ($argument instanceof FlowItemContainer) {
                $parents[] = $argument;
                foreach ($argument->getItems() as $action) {
                    $this->removeDirectActionCall($action, $item, $parents);
                }
                return;
            }
        }

        $newContents = [];
        foreach ($item->serializeContents() as $data) {
            if (!is_string($data) or (!str_contains($data, "{") and !str_contains($data, "}"))) {
                $newContents[] = $data;
                continue;
            }

            $dataPrev = $data;
            for ($i = 0; $i < 5; $i ++) {
                try {
                    $tokens = (new EvaluableStringLexer())->lexer($data);
                    $ast = (new LegacyVariableParser())->parse($tokens);
                } catch (VariableParseException $e) {
                    Main::getInstance()->getLogger()->error("Failed to parse variable during upgrading recipe: ".$e->getMessage()."; ".$data);
                    continue;
                }

                $data = $this->removeDirectActionCallNested((string)$ast, $ast, $item, $parentItem, $parents);

                if ($dataPrev === $data) {
                    break;
                }
                $dataPrev = $data;
            }

            $newContents[] = $data;
        }
        $item->loadSaveData($newContents);
        array_pop($parents);
    }

    /**
     * @param string $data
     * @param Node $node
     * @param FlowItem $item
     * @param FlowItem|null $parentItem
     * @param FlowItemContainer[] $parentContainers
     * @return string
     */
    private function removeDirectActionCallNested(string $data, Node $node, FlowItem $item, ?FlowItem $parentItem, array $parentContainers): string {
        if ($node instanceof WrappedNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getStatement(), $item, $parentItem, $parentContainers);
        }
        if ($node instanceof ToStringNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getNode(), $item, $parentItem, $parentContainers);
        }
        if ($node instanceof BinaryExpressionNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getLeft(), $item, $parentItem, $parentContainers);
            $data = $this->removeDirectActionCallNested($data, $node->getRight(), $item, $parentItem, $parentContainers);
        }
        if ($node instanceof UnaryExpressionNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getRight(), $item, $parentItem, $parentContainers);
        }
        if ($node instanceof PropertyNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getLeft(), $item, $parentItem, $parentContainers);
            $data = $this->removeDirectActionCallNested($data, $node->getIdentifier(), $item, $parentItem, $parentContainers);
        }
        if ($node instanceof MethodNode) {
            $data = $this->removeDirectActionCallNested($data, $node->getLeft(), $item, $parentItem, $parentContainers);
            $data = $this->removeDirectActionCallNested($data, $node->getIdentifier(), $item, $parentItem, $parentContainers);
            foreach ($node->getArguments() as $arg) {
                $data = $this->removeDirectActionCallNested($data, $arg, $item, $parentItem, $parentContainers);
            }
        }
        if ($node instanceof ConcatenateNode) {
            foreach ($node->getNodes() as $n) {
                $data = $this->removeDirectActionCallNested($data, $n, $item, $parentItem, $parentContainers);
            }
        }

        if ($node instanceof GlobalMethodNode) {
            $name = (string)$node->getIdentifier();

            $args = [];
            foreach ($node->getArguments() as $argument) {
                $dataPrev = $data;
                if ($argument instanceof ToStringNode or $argument instanceof ConcatenateNode) {
                    $data = $this->removeDirectActionCallNested($data, $argument, $item, $parentItem, $parentContainers);
                }
                if ($data !== $dataPrev) return $data;

                $args[] = (string)$argument;
            }

            $newAction = null;
            $resultName = "";
            switch ($name) {
                case FlowItemIds::CREATE_ITEM_VARIABLE:
                    $resultName = $args[3] ?? "item_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new CreateItemVariable($args[0] ?? "", (int)($args[1] ?? 0), $args[2] ?? "", $resultName);
                    break;
                case FlowItemIds::SET_ITEM_LORE:
                    $resultName = $args[0] ?? "item_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new SetItemLore($resultName, $args[1] ?? "");
                    break;
                case FlowItemIds::ADD_ENCHANTMENT:
                    $resultName = $args[0] ?? "item_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new AddEnchantment($resultName, $args[1] ?? "", (int)($args[2] ?? 1));
                    break;
                case FlowItemIds::CREATE_BLOCK_VARIABLE:
                    $resultName = $args[1] ?? "block_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new CreateBlockVariable($args[0] ?? "", $resultName);
                    break;
                case FlowItemIds::CREATE_POSITION_VARIABLE:
                    $resultName = $args[4] ?? "pos_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new CreatePositionVariable((float)($args[0] ?? 0), (float)($args[1] ?? 0), (float)($args[2] ?? 0), $args[3] ?? "", $resultName);
                    break;
                case FlowItemIds::FOUR_ARITHMETIC_OPERATIONS:
                    $resultName = $args[3] ?? "result_".str_replace("-", "", Uuid::uuid4()->toString());
                    $newAction = new FourArithmeticOperations((float)($args[0] ?? 0), (int)($args[1] ?? 0), (float)($args[2] ?? 0), $resultName);
                    break;
            }

            if ($newAction !== null) {
                $this->insertActionBefore($item, $newAction, $parentItem, $parentContainers);

                if ($newAction->getReturnValueType() === FlowItem::RETURN_VARIABLE_VALUE) {
                    $data = str_replace((string)$node, $resultName, $data);
                } else {
                    $data = str_replace("{".$node."}", $resultName, $data);
                }
            }
        }

        return $data;
    }

    private function insertActionBefore(FlowItem $item, FlowItem $action, ?FlowItem $parentItem, array $parents): void {
        $container = array_pop($parents);
        if ($container instanceof Recipe) {
            $index = array_search($item, $container->getItems(), true);
        } else {
            $container = array_pop($parents);
            $index = array_search($parentItem, $container->getItems(), true);
        }

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