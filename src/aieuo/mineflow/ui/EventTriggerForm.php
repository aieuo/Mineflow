<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\TriggerForm;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Button;

class EventTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, array $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger[1]])))
            ->setContent("type: ".$trigger[0]."\n@trigger.event.".$trigger[1])
            ->addButtons([
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new TriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    default:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendEventTriggerList(Player $player, Recipe $recipe) {
        $events = array_filter(Main::getInstance()->getEvents()->getAll(), function ($value) {
            return $value;
        });
        $buttons = [new Button("@form.back")];
        foreach ($events as $event => $value) {
            $buttons[] = new Button("@trigger.event.".$event);
        }
        (new ListForm(Language::get("trigger.event.list.title", [$recipe->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, Recipe $recipe, array $events) {
                if ($data === null) return;

                if ($data === 0) {
                    (new TriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }
                $data --;

                $event = $events[$data];
                $this->sendSelectEventTrigger($player, $recipe, $event);
            })->addArgs($recipe, array_keys($events))->show($player);
    }

    public function sendSelectEventTrigger(Player $player, Recipe $recipe, string $eventName) {
        $event = TriggerManager::getManager(TriggerManager::TRIGGER_EVENT)->getEvents()[$eventName];
        (new ListForm(Language::get("trigger.event.select.title", [$recipe->getName(), $eventName])))
            ->setContent($eventName."\n@".$event[1]) // TODO: イベントの詳しい説明
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onRecive(function (Player $player, ?int $data, Recipe $recipe, string $eventName) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->sendEventTriggerList($player, $recipe);
                    return;
                }

                $trigger = [TriggerManager::TRIGGER_EVENT, $eventName];
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->addArgs($recipe, $eventName)->show($player);
    }

}