<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\button\CommandButton;
use aieuo\mineflow\formAPI\element\mineflow\button\CommandConsoleButton;
use aieuo\mineflow\formAPI\element\mineflow\button\FormButton;
use aieuo\mineflow\formAPI\element\mineflow\button\RecipeButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use function str_starts_with;

class CustomListFormForm {

    public function sendMenu(Player $player, ListForm $form, array $messages = []): void {
        (new ListForm($form->getName()))
            ->addButton(new Button("@form.back", function () use($player) {
                $prev = Session::getSession($player)->get("form_menu_prev");
                is_callable($prev) ? $prev($player) : (new CustomFormForm())->sendMenu($player);
            }))->addButton(new Button("@form.form.formMenu.preview", fn() => (new CustomFormForm())->previewForm($player, $form)))
            ->addButton(new Button("@form.recipe.recipeMenu.execute", fn() => (new CustomFormForm())->executeForm($player, $form)))
            ->addButton(new Button("@form.form.formMenu.changeTitle", fn() => (new CustomFormForm())->sendChangeFormTitle($player, $form)))
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
        $hasConsoleCommandPermission = Mineflow::getPlayerSettings()->hasPlayerActionPermission($player->getName(), FlowItemPermission::CONSOLE);
        (new ListForm("@customForm.list.addButton"))
            ->addButton(new Button("@form.back", fn() => $this->sendMenu($player, $form)))
            ->addButton(new Button("@customForm.list.button.type.normal", fn() => $this->sendAddButton($player, $form)))
            ->addButton(new Button("@customForm.list.button.type.command", fn() => $this->sendAddCommandButton($player, $form, false)))
            ->addButton(new Button("@customForm.list.button.type.commandConsole", fn() => $this->sendAddCommandButton($player, $form, true)), $hasConsoleCommandPermission)
            ->addButton(new Button("@customForm.list.button.type.form", fn() => $this->sendAddFormButton($player, $form)))
            ->addButton(new Button("@customForm.list.button.type.recipe", fn() => $this->sendAddRecipeButton($player, $form)))
            ->show($player);
    }

    public function sendAddButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), ""),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function (Player $player, array $data, ListForm $form) {
                $imageType = str_starts_with($data[1], "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                $image = $data[1] === "" ? null : new ButtonImage($data[1], $imageType);
                $form->addButton(new Button($data[0], null, $image));
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendAddCommandButton(Player $player, ListForm $form, bool $consoleCommandButton): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true, $buttonText),
                new Input("@customForm.list.commandButton.command", "", "", true, $command),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), "", false, $imagePath),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function () use($player, $form, $consoleCommandButton, &$buttonText, &$command, &$imagePath) {
                $imageType = str_starts_with($imagePath, "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                $image = $imagePath === "" ? null : new ButtonImage($imagePath, $imageType);
                $button = $consoleCommandButton
                    ? new CommandConsoleButton($command, $buttonText, null, $image)
                    : new CommandButton($command, $buttonText, null, $image);

                $form->addButton($button);
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->show($player);
    }

    public function sendAddFormButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true, $buttonText),
                new Input("@customForm.list.formButton.form", "", "", true, $formName),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), "", false, $imagePath),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function () use($player, $form, &$buttonText, &$formName, &$imagePath) {
                $imageType = str_starts_with($imagePath, "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                $image = $imagePath === "" ? null : new ButtonImage($imagePath, $imageType);
                $button = new FormButton($formName, $buttonText, null, $image);

                $form->addButton($button);
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->show($player);
    }

    public function sendAddRecipeButton(Player $player, ListForm $form): void {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text", "", "", true, $buttonText),
                new Input("@customForm.list.recipeButton.recipe", "", "", true, $recipeName),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), "", false, $imagePath),
                new CancelToggle(fn() => $this->sendButtonList($player, $form, ["@form.canceled"])),
            ])->onReceive(function () use($player, $form, &$buttonText, &$recipeName, &$imagePath) {
                $imageType = str_starts_with($imagePath, "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                $image = $imagePath === "" ? null : new ButtonImage($imagePath, $imageType);
                $button = new RecipeButton($recipeName, $buttonText, null, $image);

                $form->addButton($button);
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, ["@form.added"]);
            })->show($player);
    }

    public function sendEditButton(Player $player, ListForm $form, Button $button, int $index): void {
        if ($button instanceof CommandButton) {
            $this->sendEditCommandButton($player, $form, $button, $index);
            return;
        }
        if ($button instanceof FormButton) {
            $this->sendEditFormButton($player, $form, $button, $index);
            return;
        }
        if ($button instanceof RecipeButton) {
            $this->sendEditRecipeButton($player, $form, $button, $index);
            return;
        }

        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true, $buttonText),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), $button->getImage()?->getData() ?? "", false, $iconPath),
                new CancelToggle(null, "@form.delete", false, $delete),
            ])->onReceive(function () use($player, $form, $index, $button, &$buttonText, &$iconPath, &$delete) {
                if ($delete) {
                    $form->removeButton($index);
                } else {
                    $button->setText($buttonText);
                    if ($iconPath !== "") {
                        $buttonType = str_starts_with($iconPath, "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                        $button->setImage(new ButtonImage($iconPath, $buttonType));
                    } else {
                        $button->setImage(null);
                    }
                }
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$delete ? "@form.deleted" : "@form.changed"]);
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
                    if ($data[3] !== "") {
                        $buttonType = str_starts_with($data[3], "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                        $button->setImage(new ButtonImage($data[3], $buttonType));
                    } else {
                        $button->setImage(null);
                    }
                }
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[3] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

    public function sendEditFormButton(Player $player, ListForm $form, FormButton $button, int $index): void {
        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true),
                new Input("@customForm.list.formButton.form", "", $button->getFormName(), true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), $button->getImage() === null ? "" : $button->getImage()->getData()),
                new CancelToggle(null, "@form.delete"),
            ])->onReceive(function (Player $player, array $data) use($form, $index, $button) {
                if ($data[4]) {
                    $form->removeButton($index);
                } else {
                    $button->setText($data[1]);
                    $button->setFormName($data[2]);
                    if ($data[3] !== "") {
                        $buttonType = str_starts_with($data[3], "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                        $button->setImage(new ButtonImage($data[3], $buttonType));
                    } else {
                        $button->setImage(null);
                    }
                }
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[3] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

    public function sendEditRecipeButton(Player $player, ListForm $form, RecipeButton $button, int $index): void {
        (new CustomForm($button->getText()))
            ->setContents([
                new Label(Language::get("customForm.receive", [$index])."\n".Language::get("customForm.receive.list.button", [$button->getText()])),
                new Input("@customForm.text", "", $button->getText(), true),
                new Input("@customForm.list.recipeButton.recipe", "", $button->getRecipeName(), true),
                new Input("@customForm.image", Language::get("form.example", ["textures/items/apple"]), $button->getImage() === null ? "" : $button->getImage()->getData()),
                new CancelToggle(null, "@form.delete"),
            ])->onReceive(function (Player $player, array $data) use($form, $index, $button) {
                if ($data[4]) {
                    $form->removeButton($index);
                } else {
                    $button->setText($data[1]);
                    $button->setRecipeName($data[2]);
                    if ($data[3] !== "") {
                        $buttonType = str_starts_with($data[3], "http") ? ButtonImage::TYPE_URL : ButtonImage::TYPE_PATH;
                        $button->setImage(new ButtonImage($data[3], $buttonType));
                    } else {
                        $button->setImage(null);
                    }
                }
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendButtonList($player, $form, [$data[3] ? "@form.deleted" : "@form.changed"]);
            })->show($player);
    }

}