<?php
declare(strict_types=1);


namespace aieuo\mineflow\ui\controller;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;
use function array_pop;
use function count;

class FlowItemFormController {

    /** @var array<string, ?Recipe>  */
    private static array $editingRecipes = [];


    /** @var array<string, FlowItemContainer[]>  */
    private static array $editingContainers = [];

    /** @var array<string, FlowItem[]>  */
    private static array $editingItems = [];

    public static function clear(Player $player): void {
        self::$editingRecipes[$player->getName()] = null;
        self::$editingContainers[$player->getName()] = [];
        self::$editingItems[$player->getName()] = [];
    }

    public static function enterRecipe(Player $player, Recipe $recipe): void {
        self::clear($player);
        self::$editingRecipes[$player->getName()] = $recipe;
    }

    public static function leaveRecipe(Player $player): void {
        self::clear($player);
    }

    public static function enterContainer(Player $player, FlowItemContainer $container): void {
        self::$editingContainers[$player->getName()][] = $container;
    }

    public static function leaveContainer(Player $player): void {
        $containers = self::$editingContainers[$player->getName()] ?? [];
        array_pop($containers);
        self::$editingContainers[$player->getName()] = $containers;
    }

    public static function enterItem(Player $player, FlowItem $item): void {
        self::$editingItems[$player->getName()][] = $item;
    }

    public static function leaveItem(Player $player): void {
        $items = self::$editingItems[$player->getName()] ?? [];
        array_pop($items);
        self::$editingItems[$player->getName()] = $items;
    }

    public static function isInContainer(Player $player): bool {
        return self::getEditingContainer($player) !== null;
    }

    public static function beginEditingItem(Player $player, FlowItemContainer $container, FlowItem $item): void {
        Await::g2c(self::beginEditingItemAsync($player, $container, $item));
    }

    public static function beginEditingItemAsync(Player $player, FlowItemContainer $container, FlowItem $item): \Generator {
        self::enterItem($player, $item);
        yield from (new FlowItemForm())->sendAddedItemMenu($player, $container, $item);
    }

    public static function endEditingItem(Player $player, array $messages = []): void {
        Await::g2c(self::endEditingItemAsync($player, $messages));
    }

    public static function endEditingItemAsync(Player $player, array $messages = []): \Generator {
        self::leaveItem($player);

        $container = self::getEditingContainer($player);
        if ($container !== null) {
            yield from (new FlowItemContainerForm())->sendActionList($player, $container, $messages);
        }
    }

    public static function getEditingRecipe(Player $player): ?Recipe {
        return self::$editingRecipes[$player->getName()] ?? null;
    }

    public static function getEditingContainers(Player $player): array {
        return self::$editingContainers[$player->getName()] ?? [];
    }

    public static function getEditingContainer(Player $player): ?FlowItemContainer {
        $items = self::getEditingContainers($player);
        return count($items) === 0 ? null : $items[count($items) - 1];
    }


    public static function getEditingItems(Player $player): array {
        return self::$editingItems[$player->getName()] ?? [];
    }

    public static function getEditingItem(Player $player): ?FlowItem {
        $items = self::getEditingItems($player);
        return count($items) === 0 ? null : $items[count($items) - 1];
    }

    public static function getParentContainerOf(Player $player, FlowItemContainer $container): ?FlowItemContainer {
        $items = self::$editingContainers[$player->getName()] ?? [];
        for ($i = 1; $i < count($items); $i ++) {
            if ($items[$i] === $container) {
                return $items[$i - 1];
            }
        }
        return null;
    }

    public static function getParentContainerName(Player $player): string {
        $items = self::getEditingItems($player);
        if (count($items) <= 1) {
            return self::getEditingRecipe($player)?->getName() ?? "";
        }

        $item = $items[count($items) - 2];
        return empty($item->getCustomName()) ? $item->getName() : $item->getCustomName();
    }

}