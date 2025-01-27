<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\BaseTriggerForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerForm;
use aieuo\mineflow\ui\HomeForm;
use aieuo\mineflow\ui\MineflowForm;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\player\Player;

class EventTriggerForm extends TriggerForm {

    public function buildAddedTriggerMenu(ListForm $form, Player $player, Recipe $recipe, Trigger $trigger): void {
        if (!($trigger instanceof EventTrigger)) return;

        $form->appendContent("@trigger.event.variable", true);
        $form->forEach($trigger->getVariablesDummy(), function (ListForm $form, DummyVariable $var, string $name) {
            $form->appendContent("{".$name."} (type=".$var->getValueType().")");
        });
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        $this->sendEventTriggerList($player, $recipe);
    }

    public function sendEventTriggerList(Player $player, Recipe $recipe): void {
        $events = Mineflow::getEventManager()->getEnabledEvents();
        $buttons = [new Button("@form.back")];
        foreach ($events as $event => $value) {
            $buttons[] = new Button((string)EventTrigger::get($event));
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
            ->setContent((string)EventTrigger::get($eventName))
            ->appendContent("@trigger.event.variable", true)
            ->forEach(EventTrigger::get($eventName)->getVariablesDummy(), function (ListForm $form, DummyVariable $var, string $name) {
                $form->appendContent("{".$name."} (type = ".$var->getValueType().")");
            })->addButtons([
                new Button("@form.back", fn() => $this->sendEventTriggerList($player, $recipe)),
                new Button("@form.add", function () use($player, $recipe, $eventName) {
                    $trigger = EventTrigger::get($eventName);
                    if ($trigger === null) return;

                    (new BaseTriggerForm)->tryAddTriggerToRecipe($player, $recipe, $trigger);
                }),
            ])->show($player);
    }

    public function sendSelectEvent(Player $player): void {
        $events = Mineflow::getEventManager()->getEnabledEvents();
        $buttons = [];
        foreach ($events as $event => $value) {
            $buttons[] = new Button((string)EventTrigger::get($event), fn() => $this->sendRecipeList($player, $event));
        }
        (new ListForm("@form.event.list.title"))
            ->addButton(new Button("@form.back", fn() => (new HomeForm)->sendMenu($player)))
            ->addButtons($buttons)
            ->show($player);
    }

    public function sendRecipeList(Player $player, string $event, array $messages = []): void {
        $buttons = [new Button("@form.back"), new Button("@form.add")];

        $recipes = Mineflow::getEventManager()->getAssignedRecipes($event);
        foreach ($recipes as $recipe) {
            $buttons[] = new Button($recipe->getPathname());
        }
        (new ListForm(Language::get("form.recipes.title", [(string)EventTrigger::get($event)])))
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) use($event, $recipes) {
                switch ($data) {
                    case 0:
                        $this->sendSelectEvent($player);
                        return;
                    case 1:
                        (new MineflowForm)->selectRecipe($player, Language::get("form.recipes.add", [Language::get("trigger.event.".$event)]),
                            function (Recipe $recipe) use ($player, $event) {
                                $trigger = EventTrigger::get($event);
                                if ($recipe->existsTrigger($trigger)) {
                                    $this->sendRecipeList($player, $event, ["@trigger.alreadyExists"]);
                                    return;
                                }
                                $recipe->addTrigger($trigger);
                                $this->sendRecipeList($player, $event, ["@form.added"]);
                            },
                            fn() => $this->sendRecipeList($player, $event)
                        );
                        return;
                }
                $data -= 2;

                $recipe = $recipes[$data];
                $this->sendRecipeMenu($player, $event, $recipe->getPathname());
            })->addMessages($messages)->show($player);
    }

    public function sendRecipeMenu(Player $player, string $event, string $recipeName): void {
        (new ListForm(Language::get("form.recipes.title", [(string)EventTrigger::get($event)])))
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.edit")
            ])->onReceive(function (Player $player, int $data) use($event, $recipeName) {
                if ($data === 0) {
                    $this->sendRecipeList($player, $event);
                } elseif ($data === 1) {
                    Session::getSession($player)->set("recipe_menu_prev", function() use($player, $event, $recipeName) {
                        $this->sendRecipeMenu($player, $event, $recipeName);
                    });
                    [$name, $group] = Mineflow::getRecipeManager()->parseName($recipeName);
                    $recipe = Mineflow::getRecipeManager()->get($name, $group);
                    (new RecipeForm())->sendTriggerList($player, $recipe);
                }
            })->show($player);
    }
}