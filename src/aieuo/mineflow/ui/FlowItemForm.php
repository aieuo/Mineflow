<?php
declare(strict_types=1);

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\editor\FlowItemEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\GeneratorButton;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\controller\FlowItemFormController;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use function array_filter;
use function array_search;
use function array_values;
use function count;
use function in_array;
use function ltrim;
use function stripos;
use function trim;

class FlowItemForm {

    public function sendAddedItemMenu(Player $player, FlowItemContainer $container, FlowItem $item, array $messages = []): \Generator {
        $buttons = [];
        $buttons[] = new GeneratorButton("@form.back", function () use ($player) {
            yield from FlowItemFormController::endEditingItemAsync($player);
        });
        foreach ($item->getEditors() as $editor) {
            $buttons[] = new GeneratorButton($editor->getButtonText(), function () use ($player, $container, $item, $editor) {
                $recipe = FlowItemFormController::getEditingRecipe($player);
                if ($recipe === null) return;

                $variables = $recipe->getAddingVariablesUntil($item);

                $editor->onStartEdit($player);
                $result = yield from $editor->edit($player, $variables, false);
                $editor->onFinishEdit($player);
                if ($result === FlowItemEditor::EDIT_CLOSE) return;

                yield from $this->sendAddedItemMenu($player, $container, $item, [$result === FlowItemEditor::EDIT_SUCCESS ? "@form.changed" : "@form.cancelled"]);
            });
        }
        $buttons[] = new GeneratorButton("@form.home.rename.title", function () use ($player, $container, $item) {
            yield from $this->sendChangeName($player, $container, $item);
        });
        $buttons[] = new GeneratorButton("@form.move", function () use ($player, $container, $item) {
            yield from (new FlowItemContainerForm)->sendMoveAction($player, $container, array_search($item, $container->getItems(), true));
        });
        $buttons[] = new GeneratorButton("@form.duplicate", function () use ($player, $container, $item) {
            $newItem = clone $item;
            $container->addItem($newItem);
            yield from FlowItemFormController::endEditingItemAsync($player, ["@form.duplicate.success"]);
        });
        $buttons[] = new GeneratorButton("@form.delete", function () use ($player, $container, $item) {
            yield from $this->sendConfirmDelete($player, $container, $item);
        });

        $name = FlowItemFormController::getParentContainerName($player);
        $form = new ListForm(Language::get("form.{$container->getContainerItemType()}.addedItemMenu.title", [$name, $item->getName()]));
        $form->setContent(trim($item->getCustomName()."\n\n".ltrim($item->getDetail())));
        $form->addButtons($buttons);
        $form->addMessages($messages);

        $selected = yield from $form->showAwait($player);
        $button = $buttons[$selected];

        yield from $button->getGenerator($player);
    }

    public function selectActionCategory(Player $player, FlowItemContainer $container): \Generator {
        $buttons = [
            new GeneratorButton("@form.back", function () use ($player, $container) {
                yield from (new FlowItemContainerForm())->sendActionList($player, $container);
            }),
            new GeneratorButton("@form.items.category.favorite", function () use ($player, $container) {
                $favorites = Mineflow::getPlayerSettings()->getFavorites($player->getName(), $container->getContainerItemType());
                $actions = [];
                foreach ($favorites as $favorite) {
                    $action = FlowItemFactory::get($favorite);
                    if ($action === null) continue;

                    $actions[] = $action;
                }
                Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.favorite"));
                yield from $this->sendSelectAction($player, $container, $actions);
            })
        ];

        foreach (FlowItemCategory::root() as $category) {
            $buttons[] = $this->getCategoryButton($player, $category, FlowItemCategory::name($category), $container);
        }

        $buttons[] = new GeneratorButton("@form.search", fn() => yield from $this->sendSearchAction($player, $container));

        $name = FlowItemFormController::getParentContainerName($player);
        $selected = yield from (new ListForm(Language::get("form.{$container->getContainerItemType()}.category.title", [$name])))
            ->addButtons($buttons)
            ->showAwait($player);
        $button = $buttons[$selected];

        yield from $button->getGenerator($player);
    }

    private function getCategoryButton(Player $player, string $category, string $text, FlowItemContainer $container): Button {
        return new GeneratorButton($text, function () use ($player, $container, $category) {
            yield from $this->onSelectCategory($player, $category, $container);
        });
    }

    private function onSelectCategory(Player $player, string $category, FlowItemContainer $container): \Generator {
        $isCondition = ($container->getContainerItemType() === FlowItemContainer::CONDITION);
        $actions = FlowItemFactory::getByFilter($category, Mineflow::getPlayerSettings()->getPlayerActionPermissions($player->getName()), !$isCondition, $isCondition);

        Session::getSession($player)->set("flowItem_category", FlowItemCategory::name($category));
        yield from $this->sendSelectAction($player, $container, $actions, $category);
    }

    public function sendSearchAction(Player $player, FlowItemContainer $container): \Generator {
        $name = FlowItemFormController::getParentContainerName($player);
        $form = new CustomForm(Language::get("form.{$container->getContainerItemType()}.search.title", [$name]));
        $form->setContents([
            new Input("@form.items.search.keyword", "", Session::getSession($player)->get("flowItem_search", ""), true, result: $keyword),
            new CancelToggle(result: $canceled)
        ]);
        yield from $form->showAwait($player);

        if ($canceled) {
            yield from $this->selectActionCategory($player, $container);
            return;
        }

        $isCondition = ($container->getContainerItemType() === FlowItemContainer::CONDITION);
        $permissions = Mineflow::getPlayerSettings()->getPlayerActionPermissions($player->getName());
        $actions = FlowItemFactory::getByFilter(null, $permissions, !$isCondition, $isCondition);
        $actions = array_values(array_filter($actions, fn(FlowItem $item) => stripos($item->getName(), $keyword) !== false));

        Session::getSession($player)->set("flowItem_search", $keyword);
        Session::getSession($player)->set("flowItem_category", Language::get("form.items.category.search", [$keyword]));
        yield from $this->sendSelectAction($player, $container, $actions);
    }

    public function sendSelectAction(Player $player, FlowItemContainer $container, array $items, string $category = null): \Generator {
        $buttons = [
            new GeneratorButton("@form.back", function () use ($player, $container, $category) {
                if ($category !== null and ($parent = FlowItemCategory::getParent($category)) !== null) {
                    yield from $this->onSelectCategory($player, $parent, $container);
                } else {
                    yield from $this->selectActionCategory($player, $container);
                }
            })
        ];
        if ($category !== null) {
            foreach (FlowItemCategory::getChildren($category) as $child) {
                $buttons[] = $this->getCategoryButton($player, $child, "[".FlowItemCategory::name($child)."]", $container);
            }
        }
        foreach ($items as $item) {
            $buttons[] = new GeneratorButton($item->getName(), function () use ($player, $container, $items, $item) {
                Session::getSession($player)->set($container->getContainerItemType()."s", $items);
                FlowItemFormController::enterItem($player, $item);
                yield from $this->sendActionMenu($player, $container, $item);
            });
        }

        $name = FlowItemFormController::getParentContainerName($player);
        $selected = yield from (new ListForm(Language::get("form.{$container->getContainerItemType()}.select.title", [$name, $category ?? ""])))
            ->setContent(count($buttons) === 1 ? "@form.action.empty" : "@form.selectButton")
            ->addButtons($buttons)
            ->showAwait($player);
        $button = $buttons[$selected];

        yield from $button->getGenerator($player);
    }

    public function sendActionMenu(Player $player, FlowItemContainer $container, FlowItem $item, array $messages = []): \Generator {
        $favorites = Mineflow::getPlayerSettings()->getFavorites($player->getName(), $container->getContainerItemType());
        $favoriteText = in_array($item->getId(), $favorites, true) ? "@form.items.removeFavorite" : "@form.items.addFavorite";

        $buttons = [
            new GeneratorButton("@form.back", function () use ($player, $container, $item) {
                $actions = Session::getSession($player)->get($container->getContainerItemType()."s");
                FlowItemFormController::leaveItem($player);
                yield from $this->sendSelectAction($player, $container, $actions, $item->getCategory());
            }),
            new GeneratorButton("@form.add", function () use ($player, $container, $item) {
                $recipe = FlowItemFormController::getEditingRecipe($player);
                if ($recipe === null) return;

                $variables = $recipe->getAddingVariablesUntil($item);

                $editor = $item->getNewItemEditor();
                $editor->onStartEdit($player);
                $result = yield from $editor->edit($player, $variables, true);
                $editor->onFinishEdit($player);
                if ($result === FlowItemEditor::EDIT_CLOSE) return;

                if ($result === FlowItemEditor::EDIT_SUCCESS) {
                    $container->addItem($item);
                    yield from FlowItemFormController::endEditingItemAsync($player, ["@form.added"]);
                } else {
                    yield from $this->sendActionMenu($player, $container, $item, ["@form.cancelled"]);
                }
            }),
            new GeneratorButton($favoriteText, function () use ($player, $container, $item) {
                $config = Mineflow::getPlayerSettings();
                $config->toggleFavorite($player->getName(), $container->getContainerItemType(), $item->getId());
                $config->save();
                yield from $this->sendActionMenu($player, $container, $item, ["@form.changed"]);
            }),
        ];

        $name = FlowItemFormController::getParentContainerName($player);
        $selected = yield from (new ListForm(Language::get("form.{$container->getContainerItemType()}.menu.title", [$name, $item->getId()])))
            ->setContent($item->getDescription())
            ->addButtons($buttons)
            ->addMessages($messages)
            ->showAwait($player);
        $button = $buttons[$selected];

        yield from $button->getGenerator($player);
    }

    public function sendConfirmDelete(Player $player, FlowItemContainer $container, FlowItem $action): \Generator {
        $name = FlowItemFormController::getParentContainerName($player);
        $form = new ModalForm(Language::get("form.items.delete.title", [$name, $action->getName()]));
        $form->setContent(Language::get("form.delete.confirm", [trim($action->getDetail())]));
        $result = yield from $form->showAwait($player);

        if ($result) {
            $index = array_search($action, $container->getItems(), true);
            $container->removeItem($index);
            yield from FlowItemFormController::endEditingItemAsync($player, ["@form.deleted"]);
        } else {
            yield from $this->sendAddedItemMenu($player, $container, $action, ["@form.cancelled"]);
        }
    }

    public function sendChangeName(Player $player, FlowItemContainer $container, FlowItem $item): \Generator {
        $form = new CustomForm(Language::get("form.recipe.changeName.title", [$item->getName()]));
        $form->setContents([
            new Input("@form.recipe.changeName.content1", "", $item->getCustomName(), result: $newName),
            new CancelToggle(result: $canceled),
        ]);
        yield from $form->showAwait($player);

        if ($canceled) {
            yield from $this->sendAddedItemMenu($player, $container, $item, ["@form.cancelled"]);
            return;
        }

        $item->setCustomName($newName);
        yield from $this->sendAddedItemMenu($player, $container, $item, ["@form.changed"]);
    }
}