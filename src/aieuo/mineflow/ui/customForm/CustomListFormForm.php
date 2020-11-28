<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\Player;

class CustomListFormForm {

    public function sendMenu(Player $player, ListForm $form, array $messages = []): void {
        (new ListForm($form->getName()))
            ->addButton(new Button("@form.back", function () use($player) {
                $prev = Session::getSession($player)->get("form_menu_prev");
                is_callable($prev) ? $prev($player) : (new CustomFormForm())->sendMenu($player);
            }))->addButton(new Button("@form.form.formMenu.preview", function () use ($player, $form) {
                $form->onReceive(function (Player $player) use ($form) {
                    $this->sendMenu($player, $form);
                })->onClose(function (Player $player) use ($form) {
                    $this->sendMenu($player, $form);
                })->show($player);
            }))->addButton(new Button("@form.recipe.recipeMenu.execute", function () use ($player, $form) {
                $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form)->show($player);
            }))->addButton(new Button("@form.form.formMenu.changeTitle", function () use ($player, $form) {
                (new CustomFormForm())->sendChangeFormTitle($player, $form);
            }))->addButton(new Button("@form.form.formMenu.editContent", function () use ($player, $form) {
                (new CustomFormForm())->sendChangeFormContent($player, $form);
            }))->addButton(new Button("@customForm.list.editButton", function () use ($player, $form) {
                $this->sendButtonList($player, $form);
            }))->addButton(new Button("@form.form.formMenu.changeName", function () use ($player, $form) {
                (new CustomFormForm())->sendChangeFormName($player, $form);
            }))->addButton(new Button("@form.form.recipes", function () use ($player, $form) {
                (new CustomFormForm())->sendRecipeList($player, $form);
            }))->addButton(new Button("@form.delete", function () use ($player, $form) {
                (new CustomFormForm())->sendConfirmDelete($player, $form);
            }))->addMessages($messages)->show($player);
    }

    public function sendButtonList(Player $player, ListForm $form, array $messages = []): void {
        (new ListForm("@customForm.list.editButton"))
            ->addButton(new Button("@form.back", function () use ($player, $form) { $this->sendMenu($player, $form); }))
            ->addButton(new Button("@customForm.list.addButton", function () use ($player, $form) { $this->sendSelectButtonType($player, $form); }))
            ->addButtonsEach($form->getButtons(), function(Button $button, int $i) use($player, $form) {
                return new Button((string)$button, function () use ($player, $form, $i, $button) {
                    $this->sendEditButton($player, $form, $button, $i);
                });
            })->addMessages($messages)->show($player);

    }

    public function sendSelectButtonType(Player $player, ListForm $form): void {
        (new ListForm("@customForm.list.addButton"))
            ->setButtons([
                new Button("@form.back", function () use($player, $form) { $this->sendMenu($player, $form); }),
                new Button("@customForm.list.button.type.normal", function () use($player, $form) { $this->sendAddButton($player, $form);}),
            ])->show($player);
    }

    public function sendAddButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true),
                new CancelToggle(function() use($player, $form) { $this->sendButtonList($player, $form, ["@form.canceled"]); }),
            ])->onReceive(function (Player $player, array $data, ListForm $form) {
                $form->addButton(new Button($data[0]));
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendEditButton(Player $player, ListForm $form, Button $button, int $index): void {
        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true),
                new CancelToggle(null, "@form.delete"),
            ])->onReceive(function (Player $player, array $data) use($form, $index, $button) {
                if ($data[2]) {
                    $form->removeButton($index);
                } else {
                    $button->setText($data[1]);
                }
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[2] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

}