<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class FormTriggerForm extends TriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.form.edit.title"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe, Trigger $trigger) {
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new BaseTriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    case 2:
                        $manager = Main::getFormManager();
                        $form = $manager->getForm($trigger->getKey());
                        (new CustomFormForm)->sendFormMenu($player, $form);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        $this->sendSelectForm($player, $recipe);
    }

    public function sendSelectForm(Player $player, Recipe $recipe, array $default = [], array $errors = []): void {
        (new CustomForm(Language::get("trigger.form.select.title", [$recipe->getName()])))
            ->setContents([
                new Input("@trigger.form.select.input", "", $default[0] ?? "", true),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, Recipe $recipe) {
                if ($data[1]) {
                    (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }

                $manager = Main::getFormManager();
                if (!$manager->existsForm($data[0])) {
                    $this->sendConfirmCreate($player, $data[0], function (bool $result) use ($player, $recipe, $data) {
                        if ($result) {
                            (new CustomFormForm)->sendAddForm($player, [$data[0]]);
                        } else {
                            $this->sendSelectForm($player, $recipe, $data, [["@trigger.form.select.notFound", 0]]);
                        }
                    });
                    return;
                }

                $form = $manager->getForm($data[0]);
                $this->sendSelectFormTriggerButton($player, $recipe, $form);
            })->addArgs($recipe)->addErrors($errors)->show($player);
    }

    public function sendSelectFormTriggerButton(Player $player, Recipe $recipe, Form $form): void {
        switch ($form) {
            case $form instanceof CustomForm:
                (new ListForm(Language::get("trigger.form.type.select", [$form->getName()])))
                    ->addButtons([
                        new Button("@form.cancelAndBack"),
                        new Button("@trigger.form.receive"),
                        new Button("@trigger.form.close"),
                    ])->onReceive(function (Player $player, int $data, Recipe $recipe, Form $form) {
                        $trigger = FormTrigger::create($form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 2:
                                $trigger->setSubKey("close");
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $recipe->addTrigger($trigger);
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    })->addArgs($recipe, $form)->show($player);
                break;
            case $form instanceof ModalForm:
                (new ListForm(Language::get("trigger.form.type.select", [$form->getName()])))
                    ->addButtons([
                        new Button("@form.cancelAndBack"),
                        new Button("@trigger.form.receive"),
                        new Button(Language::get("trigger.form.button", [$form->getButton1Text()])),
                        new Button(Language::get("trigger.form.button", [$form->getButton2Text()])),
                    ])->onReceive(function (Player $player, int $data, Recipe $recipe, Form $form) {
                        $trigger = FormTrigger::create($form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 2:
                                $trigger->setSubKey("1");
                                break;
                            case 3:
                                $trigger->setSubKey("2");
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $recipe->addTrigger($trigger);
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    })->addArgs($recipe, $form)->show($player);
                break;
            case $form instanceof ListForm:
                $buttons = [
                    new Button("@form.cancelAndBack"),
                    new Button("@trigger.form.receive"),
                    new Button("@trigger.form.close"),
                ];
                foreach ($form->getButtons() as $button) {
                    $buttons[] = new Button(Language::get("trigger.form.button", [$button->getText()]));
                }
                (new ListForm(Language::get("trigger.form.type.select", [$form->getName()])))
                    ->addButtons($buttons)
                    ->onReceive(function (Player $player, int $data, Recipe $recipe, ListForm $form) {
                        $trigger = FormTrigger::create($form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 1:
                                break;
                            case 2:
                                $trigger->setSubKey("close");
                                break;
                            default:
                                $button = $form->getButton($data - 3);
                                $trigger->setSubKey($button->getUUID());
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $recipe->addTrigger($trigger);
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    })->addArgs($recipe, $form)->show($player);
                break;
        }
    }

    public function sendConfirmCreate(Player $player, string $name, callable $callback): void {
        (new ModalForm("@trigger.command.confirmCreate.title"))
            ->setContent(Language::get("trigger.command.confirmCreate.content", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(fn(Player $player, ?bool $data) => $callback($data))
            ->show($player);
    }
}