<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\trigger\Trigger;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Button;

class FormTriggerForm {

    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []) {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent("type: ".$trigger->getType()."\n".$trigger->getKey())
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
                new Button("@trigger.form.edit.title"),
            ])->onReceive(function (Player $player, ?int $data, Recipe $recipe, Trigger $trigger) {
                if ($data === null) return;

                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new TriggerForm)->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                    case 2:
                        $manager = Main::getFormManager();
                        $form = $manager->getForm(explode(";", $trigger->getKey())[0]);
                        (new CustomFormForm)->sendFormMenu($player, $form);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendSelectForm(Player $player, Recipe $recipe, array $default = [], array $errors = []) {
        (new CustomForm(Language::get("trigger.form.select.title", [$recipe->getName()])))
            ->setContents([
                new Input("@trigger.form.select.input", "", $default[0] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, ?array $data, Recipe $recipe) {
                if ($data === null) return;

                if ($data[1]) {
                    (new TriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }

                if (empty($data[0])) {
                    $this->sendSelectForm($player, $recipe, $data, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getFormManager();
                if (!$manager->existsForm($data[0])) {
                    $this->sendConfirmCreate($player, $data[0], function (bool $result) use ($player, $recipe, $data) {
                        if ($result) {
                            (new CustomFormForm)->sendAddForm($player, [$data[0]]);
                        } else {
                            $this->sendSelectForm($player, $recipe, $data, [["@trigger.command.select.notFound", 0]]);
                        }
                    });
                    return;
                }

                $form = $manager->getForm($data[0]);
                $this->sendSelectFormTriggerButton($player, $recipe, $form);
            })->addArgs($recipe)->addErrors($errors)->show($player);
    }

    public function sendSelectFormTriggerButton(Player $player, Recipe $recipe, Form $form) {
        switch ($form) {
            case $form instanceof CustomForm:
                (new ListForm(Language::get("trigger.form.type.select", [$form->getName()])))
                    ->setContent("@form.selectButton")
                    ->addButtons([
                        new Button("@form.cancelAndBack"),
                        new Button("@trigger.form.receive"),
                        new Button("@trigger.form.close"),
                    ])->onReceive(function (Player $player, ?int $data, Recipe $recipe, Form $form) {
                        if ($data === null) return;

                        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 2:
                                $trigger->setKey($form->getName().";close");
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $manager = Main::getFormManager();
                        $recipe->addTrigger($trigger);
                        $manager->addRecipe($form->getName(), $recipe);
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    })->addArgs($recipe, $form)->show($player);
                break;
            case $form instanceof ModalForm:
                (new ListForm(Language::get("trigger.form.type.select", [$form->getName()])))
                    ->setContent("@form.selectButton")
                    ->addButtons([
                        new Button("@form.cancelAndBack"),
                        new Button("@trigger.form.receive"),
                        new Button(Language::get("trigger.form.button", [$form->getButton1()])),
                        new Button(Language::get("trigger.form.button", [$form->getButton2()])),
                    ])->onReceive(function (Player $player, ?int $data, Recipe $recipe, Form $form) {
                        if ($data === null) return;

                        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 2:
                                $trigger->setKey($form->getName().";1");
                                break;
                            case 3:
                                $trigger->setKey($form->getName().";2");
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $manager = Main::getFormManager();
                        $recipe->addTrigger($trigger);
                        $manager->addRecipe($form->getName(), $recipe);
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
                    ->setContent("@form.selectButton")
                    ->addButtons($buttons)
                    ->onReceive(function (Player $player, ?int $data, Recipe $recipe, ListForm $form) {
                        if ($data === null) return;

                        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
                        switch ($data) {
                            case 0:
                                $this->sendSelectForm($player, $recipe);
                                return;
                            case 1:
                                break;
                            case 2:
                                $trigger->setKey($form->getName().";close");
                                break;
                            default:
                                $button = $form->getButton($data - 3);
                                $trigger->setKey($form->getName().";".$button->getUUId());
                                break;
                        }
                        if ($recipe->existsTrigger($trigger)) {
                            $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                            return;
                        }
                        $manager = Main::getFormManager();
                        $recipe->addTrigger($trigger);
                        $manager->addRecipe($form->getName(), $recipe);
                        $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    })->addArgs($recipe, $form)->show($player);
                break;
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function sendConfirmCreate(Player $player, string $name, callable $callback) {
        (new ModalForm("@trigger.command.confirmCreate.title"))
            ->setContent(Language::get("trigger.command.confirmCreate.content", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, callable $callback) {
                if ($data === null) return;
                call_user_func_array($callback, [$data]);
            })->addArgs($callback)->show($player);
    }
}