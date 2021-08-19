<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\CommandButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\utils\ButtonImage;
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
                $form->onReceive(fn() => $this->sendMenu($player, $form))->onClose(fn() => $this->sendMenu($player, $form))->show($player);
            }))->addButton(new Button("@form.recipe.recipeMenu.execute", function () use ($player, $form) {
                $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form)->show($player);
            }))->addButton(new Button("@form.form.formMenu.changeTitle", fn() => (new CustomFormForm())->sendChangeFormTitle($player, $form)))
            ->addButton(new Button("@form.form.formMenu.editContent", fn() => (new CustomFormForm())->sendChangeFormContent($player, $form)))
            ->addButton(new Button("@customForm.list.editButton", fn() => $this->sendButtonList($player, $form)))
            ->addButton(new Button("@form.form.formMenu.changeName", fn() => (new CustomFormForm())->sendChangeFormName($player, $form)))
            ->addButton(new Button("@form.form.recipes", fn() => (new CustomFormForm())->sendRecipeList($player, $form)))
            ->addButton(new Button("@form.delete", fn() => (new CustomFormForm())->sendConfirmDelete($player, $form)))
            ->addMessages($messages)
            ->show($player);
    }

    public function sendButtonList(Player $player, ListForm $form, array $messages = []): void {
        (new ListForm("@customForm.list.editButton"))
            ->addButton(new Button("@form.back", fn() => $this->sendMenu($player, $form)))
            ->addButton(new Button("@customForm.list.addButton", fn() => $this->sendSelectButtonType($player, $form)))
            ->addButtonsEach($form->getButtons(), function(Button $button, int $i) use($player, $form) {
                return new Button((string)$button, function () use ($player, $form, $i, $button) {
                    $this->sendEditButton($player, $form, $button, $i);
                });
            })->addMessages($messages)->show($player);

    }

    public function sendSelectButtonType(Player $player, ListForm $form): void {
        (new ListForm("@customForm.list.addButton"))
            ->setButtons([
                new Button("@form.back", fn() => $this->sendMenu($player, $form)),
                new Button("@customForm.list.button.type.normal", fn() => $this->sendAddButton($player, $form)),
                new Button("@customForm.list.button.type.command", fn() => $this->sendAddCommandButton($player, $form)),
            ])->show($player);
    }

    public function sendAddButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), ""),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function (Player $player, array $data, ListForm $form) {
                $image = $data[1] === "" ? null : new ButtonImage($data[1], ButtonImage::TYPE_PATH);
                $form->addButton(new Button($data[0], null, $image));
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendAddCommandButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true),
                new Input("@customForm.list.commandButton.command", "", "", true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), ""),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function (Player $player, array $data, ListForm $form) {
                $image = $data[2] === "" ? null : new ButtonImage($data[2], ButtonImage::TYPE_PATH);
                $form->addButton(new CommandButton($data[1], $data[0], $image));
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendEditButton(Player $player, ListForm $form, Button $button, int $index): void {
        if ($button instanceof CommandButton) {
            $this->sendEditCommandButton($player, $form, $button, $index);
            return;
        }

        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), $button->getImage() === null ? "" : $button->getImage()->getData()),
                new CancelToggle(null, "@form.delete"),
            ])->onReceive(function (Player $player, array $data) use($form, $index, $button) {
                if ($data[3]) {
                    $form->removeButton($index);
                } else {
                    $button->setText($data[1]);
                    $button->setImage($data[2] === "" ? null : new ButtonImage($data[2], ButtonImage::TYPE_PATH));
                }
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[4] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

    public function sendEditCommandButton(Player $player, ListForm $form, CommandButton $button, int $index): void {
        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true),
                new Input("@customForm.list.commandButton.command", "", $button->getCommand(), true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), $button->getImage() === null ? "" : $button->getImage()->getData()),
                new CancelToggle(null, "@form.delete"),
            ])->onReceive(function (Player $player, array $data) use($form, $index, $button) {
                if ($data[4]) {
                    $form->removeButton($index);
                } else {
                    $button->setText($data[1]);
                    $button->setCommand($data[2]);
                    $button->setImage($data[3] === "" ? null : new ButtonImage($data[3], ButtonImage::TYPE_PATH));
                }
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[3] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

}