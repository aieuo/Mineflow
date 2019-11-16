<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\action\script\ActionScript;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\FormAPI\element\Button;
use aieuo\mineflow\recipe\Recipe;

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
            ->onRecive(function (Player $player, ?int $data, ActionContainer $container, array $actions) {
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
}