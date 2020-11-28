<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\event\EventTriggerList;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\ui\HomeForm;
use aieuo\mineflow\ui\MineflowForm;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\Player;

class EventTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent((string)$trigger)
            ->appendContent("@trigger.event.variable", true)
            ->forEach(EventTriggerList::get($trigger->getKey())->getVariablesDummy(), function (ListForm $form, DummyVariable $var) {
                $form->appendContent("{".$var->getName()."} (".$var->getValueType().")");
            })->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe, Trigger $trigger) {
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        $this->sendEventTriggerList($player, $recipe);
    }

    public function sendEventTriggerList(Player $player, Recipe $recipe): void {
        $events = Main::getEventManager()->getEnabledEvents();
        $buttons = [new Button("@form.back")];
        foreach ($events as $event => $value) {
            $buttons[] = new Button((string)EventTrigger::create($event));
        }
        (new ListForm(Language::get("trigger.event.list.title", [$recipe->getName()])))
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, Recipe $recipe, array $events) {
                if ($data === 0) {
                    (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }
                $data--;

                $event = $events[$data];
                $this->sendSelectEventTrigger($player, $recipe, $event);
            })->addArgs($recipe, array_keys($events))->show($player);
    }

    public function sendSelectEventTrigger(Player $player, Recipe $recipe, string $eventName): void {
        (new ListForm(Language::get("trigger.event.select.title", [$recipe->getName(), $eventName])))
            ->setContent((string)EventTrigger::create($eventName))
            ->appendContent("@trigger.event.variable", true)
            ->forEach(EventTriggerList::get($eventName)->getVariablesDummy(), function (ListForm $form, DummyVariable $var) {
                $form->appendContent("{".$var->getName()."} (type = ".$var->getValueType().")");
            })->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe, string $eventName) {
                if ($data === 0) {
                    $this->sendEventTriggerList($player, $recipe);
                    return;
                }

                $trigger = EventTrigger::create($eventName);
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->addArgs($recipe, $eventName)->show($player);
    }

    public function sendSelectEvent(Player $player): void {
        $events = Main::getEventManager()->getEnabledEvents();
        $buttons = [new Button("@form.back", function () use($player) { (new HomeForm)->sendMenu($player); })];
        foreach ($events as $event => $value) {
            $buttons[] = new Button((string)EventTrigger::create($event), function () use($player, $event) {
                $this->sendRecipeList($player, $event);
            });
        }
        (new ListForm("@form.event.list.title"))
            ->addButtons($buttons)
            ->show($player);
    }

    public function sendRecipeList(Player $player, string $event, array $messages = []): void {
        $buttons = [new Button("@form.back"), new Button("@form.add")];

        $recipes = Main::getEventManager()->getAssignedRecipes($event);
        foreach ($recipes as $name => $events) {
            $buttons[] = new Button($name);
        }
        (new ListForm(Language::get("form.recipes.title", [(string)EventTrigger::create($event)])))
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, string $event, array $recipes) {
                switch ($data) {
                    case 0:
                        $this->sendSelectEvent($player);
                        return;
                    case 1:
                        (new MineflowForm)->selectRecipe($player, Language::get("form.recipes.add", [Language::get("trigger.event.".$event)]),
                            function (Recipe $recipe) use ($player, $event) {
                                $trigger = EventTrigger::create($event);
                                if ($recipe->existsTrigger($trigger)) {
                                    $this->sendRecipeList($player, $event, ["@trigger.alreadyExists"]);
                                    return;
                                }
                                $recipe->addTrigger($trigger);
                                $this->sendRecipeList($player, $event, ["@form.added"]);
                            },
                            function () use ($player, $event) {
                                $this->sendRecipeList($player, $event);
                            }
                        );
                        return;
                }
                $data -= 2;

                $this->sendRecipeMenu($player, $event, array_keys($recipes)[$data]);
            })->addMessages($messages)->addArgs($event, $recipes)->show($player);
    }

    public function sendRecipeMenu(Player $player, string $event, string $recipeName): void {
        (new ListForm(Language::get("form.recipes.title", [(string)(EventTrigger::create($event))])))
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.edit")
            ])->onReceive(function (Player $player, int $data, string $event, string $recipeName) {
                if ($data === 0) {
                    $this->sendRecipeList($player, $event);
                } elseif ($data === 1) {
                    Session::getSession($player)->set("recipe_menu_prev", function() use($player, $event, $recipeName) {
                        $this->sendRecipeMenu($player, $event, $recipeName);
                    });
                    [$name, $group] = Main::getRecipeManager()->parseName($recipeName);
                    $recipe = Main::getRecipeManager()->get($name, $group);
                    (new RecipeForm())->sendTriggerList($player, $recipe);
                }
            })->addArgs($event, $recipeName)->show($player);
    }
}
