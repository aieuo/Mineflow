<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use pocketmine\utils\TextFormat;

class ConditionContainerForm {

    public function sendConditionList(Player $player, ConditionContainer $container, array $messages = []) {
        $conditions = $container->getConditions();

        $buttons = [new Button("@form.back"), new Button("@condition.add")];
        foreach ($conditions as $condition) {
            $buttons[] = new Button(trim($condition->getDetail()));
        }

        (new ListForm(Language::get("form.conditionContainer.conditionList.title", [$container->getContainerName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, ConditionContainer $container, array $conditions) {
                if ($data === 0) {
                    if ($container instanceof Recipe) {
                        (new RecipeForm)->sendRecipeMenu($player, $container);
                    } else {
                        /** @var FlowItem $container */
                        $container->sendCustomMenu($player);
                    }
                    return;
                }
                if ($data === 1) {
                    (new ConditionForm)->selectConditionCategory($player, $container);
                    return;
                }
                $data -= 2;

                /** @var Condition $condition */
                $condition = $conditions[$data];
                $session = Session::getSession($player);
                $session->set("parents", array_merge($session->get("parents"), [$container]));

                (new ConditionForm)->sendAddedConditionMenu($player, $container, $condition);
            })->addArgs($container, $conditions)->addMessages($messages)->show($player);
    }

    public function sendMoveCondition(Player $player, ConditionContainer $container, int $selected, array $messages = [], int $count = 0) {
        $conditions = $container->getConditions();
        $selectedCondition = $conditions[$selected];

        $buttons = [new Button("@form.back")];
        foreach ($conditions as $i => $condition) {
            $buttons[] = new Button(($i === $selected ? TextFormat::AQUA : "").trim($condition->getDetail()));
        }
        $buttons[] = new Button("");

        /** @var Recipe|FlowItem $container */
        (new ListForm(Language::get("form.conditionContainer.moveCondition.title", [$container->getContainerName(), $selectedCondition->getName()])))
            ->setContent("@form.conditionContainer.moveCondition.content")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, ConditionContainer $container, int $selected, array $conditions, int $count = 0) {
                $move = $conditions[$selected];
                if ($data === 0) {
                    (new ConditionForm)->sendAddedConditionMenu($player, $container, $move, [$count === 0 ? "@form.cancelled" : "@form.moved"]);
                    return;
                }
                $data -= 1;

                $conditions = (new ActionContainerForm)->getMovedContents($conditions, $selected, $data);
                $container->setConditions($conditions);
                $this->sendMoveCondition($player, $container, $selected < $data ? $data-1 : $data, ["@form.moved"], ++$count);
            })->addArgs($container, $selected, $conditions, $count)->addMessages($messages)->show($player);
    }
}