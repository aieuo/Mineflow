<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FlowItemContainerForm {

    public function sendActionList(Player $player, FlowItemContainer $container, string $type, array $messages = []): void {
        $actions = $container->getItems($type);

        $buttons = [new Button("@form.back"), new Button("@{$type}.add")];
        foreach ($actions as $action) {
            $buttons[] = new Button(empty($action->getCustomName()) ? trim(TextFormat::clean($action->getDetail())) : $action->getCustomName());
        }

        (new ListForm(Language::get("form.{$type}Container.list.title", [$container->getContainerName()])))
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data) use($container, $type, $actions) {
                if ($data === 0) {
                    if ($container instanceof Recipe) {
                        (new RecipeForm)->sendRecipeMenu($player, $container);
                    } else {
                        /** @var FlowItem $container */
                        (new FlowItemForm)->sendFlowItemCustomMenu($player, $container, $type);
                    }
                    return;
                }
                Session::getSession($player)
                    ->set("action_list_clicked", null)
                    ->push("parents", $container);

                if ($data === 1) {
                    (new FlowItemForm)->selectActionCategory($player, $container, $type);
                    return;
                }
                $data -= 2;
                $action = $actions[$data];
                Session::getSession($player)->set("action_list_clicked", $action);

                (new FlowItemForm)->sendAddedItemMenu($player, $container, $type, $action);
            })->addArgs($container, $actions)->addMessages($messages)->show($player);
    }

    public function sendMoveAction(Player $player, FlowItemContainer $container, string $type, int $selected, array $messages = [], int $count = 0): void {
        $actions = $container->getItems($type);
        $selectedAction = $actions[$selected];

        $parents = Session::getSession($player)->get("parents");
        array_pop($parents);
        /** @var FlowItemContainer|null $parent */
        $parent = array_pop($parents);

        $buttons = [
            new Button("@form.back", fn() => (new FlowItemForm)->sendAddedItemMenu($player, $container, $type, $actions[$selected], [$count === 0 ? "@form.cancelled" : "@form.moved"])),
        ];

        if ($parent instanceof FlowItemContainer) {
            $buttons[] = new Button("@action.move.outside", function (Player $player) use($parent, $container, $type, $selected, $count) {
                $tmp = $container->getItem($selected, $type);
                $container->removeItem($selected, $type);
                $parent->addItem($tmp, $type);
                Session::getSession($player)->pop("parents");
                $this->sendMoveAction($player, $parent, $type, count($parent->getItems($type)) - 1, ["@form.moved"], ++ $count);
            });
        }

        $i = 0;
        foreach ($actions as $i => $action) {
            if ($i !== $selected and $i !== $selected + 1) {
                $buttons[] = new Button("@form.move.to.here", fn() => $this->moveContent($player, $container, $type, $actions, $selected, $i, $count));
            }

            $color = ($i === $selected ? TextFormat::AQUA : "");
            $buttons[] = new Button($color.trim(TextFormat::clean($action->getDetail())), function (Player $player) use($i, $action, $container, $type, $selected, $count) {
                if ($i === $selected or !($action instanceof FlowItemContainer)) {
                    $this->sendMoveAction($player, $container, $type, $selected, ["@form.move.target.invalid"], $count);
                } else {
                    $tmp = $container->getItem($selected, $type);
                    $container->removeItem($selected, $type);
                    $action->addItem($tmp, $type);
                    Session::getSession($player)->push("parents", $action);
                    $this->sendMoveAction($player, $action, $type, count($action->getItems($type)) - 1, ["@form.moved"], ++ $count);
                }
            });
        }
        if ($selected !== count($actions) - 1) {
            $buttons[] = new Button("@form.move.to.here", fn() => $this->moveContent($player, $container, $type, $actions, $selected, $i + 1, $count));
        }

        (new ListForm(Language::get("form.{$type}Container.move.title", [$container->getContainerName(), $selectedAction->getName()])))
            ->setContent("@form.{$type}Container.move.content")
            ->addButtons($buttons)
            ->addMessages($messages)
            ->show($player);
    }

    public function moveContent(Player $player, FlowItemContainer $container, string $type, array $actions, int $from, int $to, int $count): void {
        $actions = $this->getMovedContents($actions, $from, $to);
        $container->setItems($actions, $type);
        $this->sendMoveAction($player, $container, $type, $from < $to ? $to - 1 : $to, ["@form.moved"], ++ $count);
    }

    public function getMovedContents(array $contents, int $from, int $to): array {
        $move = $contents[$from];
        if ($from < $to) $to--;
        unset($contents[$from]);
        $newContents = [];
        foreach (array_values($contents) as $i => $action) {
            if ($i === $to) $newContents[] = $move;
            $newContents[] = $action;
        }
        if (count($contents) === count($newContents)) $newContents[] = $move;
        return $newContents;
    }
}