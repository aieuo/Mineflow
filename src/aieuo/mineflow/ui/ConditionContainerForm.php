<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\condition\script\ConditionScript;
use aieuo\mineflow\condition\ConditionContainer;
use aieuo\mineflow\FormAPI\element\Button;

class ConditionContainerForm {

    public function sendConditionList(Player $player, ConditionContainer $container, array $messages = []) {
        $conditions = $container->getConditions();

        $buttons = [new Button("@form.back"), new Button("@form.recipe.conditions.add")];
        foreach ($conditions as $condition) {
            $buttons[] = new Button(trim($condition->getDetail()));
        }

        (new ListForm(Language::get("form.recipe.editConditions.title", [$container->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, ConditionContainer $container, array $conditions) {
                if ($data === null) return;

                if ($data === 0) {
                    $container->sendEditForm($player);
                    return;
                }
                if ($data === 1) {
                    (new ConditionForm)->selectConditionCategory($player, $container);
                    return;
                }
                $data -= 2;

                $condition = $conditions[$data];
                $session = Session::getSession($player);
                $session->set("parents", array_merge($session->get("parents"), [$container]));

                if ($condition instanceof ConditionScript) {
                    $condition->sendEditForm($player);
                    return;
                }
                (new ConditionForm)->sendAddedConditionMenu($player, $container, $condition);
            })->addArgs($container, $conditions)->addMessages($messages)->show($player);
    }
}