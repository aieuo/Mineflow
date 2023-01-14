<?php
declare(strict_types=1);


namespace aieuo\mineflow\recipe;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use function array_key_last;
use function array_map;
use function array_pop;
use function array_search;
use function explode;
use function get_class;
use function is_array;
use function is_string;
use function mt_rand;
use function preg_replace;
use function str_contains;
use function str_replace;
use function version_compare;
use const PHP_INT_MAX;

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

            foreach ($recipe->getItemsFlatten(FlowItemContainer::ACTION) as $action) {
                $this->replaceLevelToWorld($action);
            }
            foreach ($recipe->getItemsFlatten(FlowItemContainer::CONDITION) as $condition) {
                $this->replaceLevelToWorld($condition);
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

            foreach ($recipe->getItemsFlatten(FlowItemContainer::ACTION) as $action) {
                $this->replacePositionVariable($action);
            }
            foreach ($recipe->getItemsFlatten(FlowItemContainer::CONDITION) as $condition) {
                $this->replacePositionVariable($condition);
            }

            $recipe->setPluginVersion("3.0.0");
        }
    }

    private function replaceLevelToWorld(FlowItem $action): void {
        $newContents = [];
        foreach ($action->serializeContents() as $data) {
            if (is_string($data)) {
                $data = str_replace(["origin_level", "target_level"], ["origin_world", "target_world"], $data);
                $data = preg_replace("/({.+\.)level((\.?.+)*})/u", "$1world$2", $data);
            }
            $newContents[] = $data;
        }
        $action->loadSaveData($newContents);
    }

    private function removeDirectActionCall(FlowItem $item, array $parents): void {
        $variableHelper = Mineflow::getVariableHelper();
        
        if ($item instanceof FlowItemContainer) {
            $parents[] = $item;
            foreach ($item->getActions() as $action) {
                $this->removeDirectActionCall($action, $parents);
            }
            foreach ($item->getConditions() as $condition) {
                $this->removeDirectActionCall($condition, $parents);
            }
            return;
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

                $action = FlowItemFactory::get($ast["left"], true);
                if ($action === null) throw new \UnexpectedValueException("§cUnknown action id {$ast["left"]}");

                $class = get_class($action);
                $parameters = array_map(fn($arg) => (string)$arg, is_array($ast["right"]) ? $ast["right"] : [$ast["right"]]);

                $newAction = new $class(...$parameters);
                $returnType = $action->getReturnValueType();

                if ($returnType === FlowItem::RETURN_NONE) {
                    $this->insertActionBefore($item, $newAction, $parents);
                    $data = str_replace("{".$variable."}", "__".$variable."__", $data);
                    continue;
                }

                $reflection = new \ReflectionClass($newAction);
                try {
                    $getResultName = $reflection->getMethod("getResultName");
                    $setResultName = $reflection->getMethod("setResultName");
                } catch (\ReflectionException) {
                    try {
                        $getResultName = $reflection->getMethod("getVariableName");
                        $setResultName = $reflection->getMethod("setVariableName");
                    } catch (\ReflectionException $e) {
                        if (Mineflow::isDebug()) Main::getInstance()->getLogger()->logException($e);
                        throw new \UnexpectedValueException("§cFailed to extract direct action call: {$ast["left"]}");
                    }
                }

                if (empty($resultName = $getResultName->invoke($newAction))) {
                    $setResultName->invoke($newAction, $resultName = $ast["left"].mt_rand(0, PHP_INT_MAX));
                }

                $this->insertActionBefore($item, $newAction, $parents);

                if ($returnType === FlowItem::RETURN_VARIABLE_VALUE) {
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
        if ($container instanceof Recipe) {
            $index = array_search($item, $container->getActions(), true);
            $container->pushAction($index, $action);
        } else {
            $container1 = array_pop($parents);
            $index = array_search($container, $container1->getActions(), true);
            $container1->pushAction($index, $action);
        }
    }

    private function replacePositionVariable(FlowItem $item): void {
        if ($item instanceof PositionFlowItem) {
            foreach ($item->getPositionVariableNames() as $name => $variable) {
                $variable = preg_replace("/^(target|entity|player)\d*$/u", "$0.location", $variable);
                $item->setPositionVariableName($variable, $name);
            }
        }
    }
}
