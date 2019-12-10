<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\action\script\ActionScript;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\TextFormat;

class ActionContainerForm {

    public function sendActionList(Player $player, ActionContainer $container, array $messages = []) {
        $actions = $container->getActions();

        $buttons = [new Button("@form.back"), new Button("@action.add")];
        foreach ($actions as $action) {
            $buttons[] = new Button(trim($action->getDetail()));
        }

        (new ListForm(Language::get("form.actionContainer.actionList.title", [$container->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, ?int $data, ActionContainer $container, array $actions) {
                if ($data === null) return;

                if ($data === 0) {
                    if ($container instanceof Recipe) {
                        (new RecipeForm)->sendRecipeMenu($player, $container);
                    } else {
                        $container->sendEditForm($player);
                    }
                    return;
                }
                if ($data === 1) {
                    (new ActionForm)->selectActionCategory($player, $container);
                    return;
                }
                $data -= 2;

                $action = $actions[$data];
                $session = Session::getSession($player);
                $session->set("parents", array_merge($session->get("parents"), [$container]));

                if ($action instanceof ActionScript) {
                    $action->sendEditForm($player);
                    return;
                }
                (new ActionForm)->sendAddedActionMenu($player, $container, $action);
            })->addArgs($container, $actions)->addMessages($messages)->show($player);
    }

    public function sendMoveAction(Player $player, ActionContainer $container, int $selected, array $messages = [], int $count = 0) {
        $actions = $container->getActions();
        $selectedAction = $actions[$selected];

        $buttons = [new Button("@form.back")];
        foreach ($actions as $i => $action) {
            $buttons[] = new Button(($i === $selected ? TextFormat::AQUA : "").trim($action->getDetail()));
        }
        $buttons[] = new Button("");

        (new ListForm(Language::get("form.actionContainer.moveAction.title", [$container->getName(), $selectedAction->getName()])))
            ->setContent("@form.actionContainer.moveAction.content")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, ?int $data, ActionContainer $container, int $selected, array $actions, int $count = 0) {
                if ($data === null) return;

                $move = $actions[$selected];
                if ($data === 0) {
                    (new ActionForm)->sendAddedActionMenu($player, $container, $move, [$count === 0 ? "@form.cancelled" : "@form.moved"]);
                    return;
                }
                $data -= 1;

                $actions = $this->getMovedContents($actions, $selected, $data);
                $container->setActions($actions);
                $this->sendMoveAction($player, $container, $selected < $data ? $data-1 : $data, ["@form.moved"], ++$count);
            })->addArgs($container, $selected, $actions, $count)->addMessages($messages)->show($player);
    }

    public function getMovedContents(array $contents, int $from, int $to): array {
        $move = $contents[$from];
        if ($from < $to) $to --;
        unset($contents[$from]);
        $newContents = [];
        foreach (array_values($contents) as $i => $action) {
            var_dump($i, $to);
            if ($i === $to) $newContents[] = $move;
            $newContents[] = $action;
        }
        if (count($contents) === count($newContents)) $newContents[] = $move;
        return $newContents;
    }
}