<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;

class CustomModalFormForm {
    public function sendMenu(Player $player, ModalForm $form, array $messages = []): void {
        (new ListForm(Language::get($form->getName())))
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.form.formMenu.preview"),
                new Button("@form.recipe.recipeMenu.execute"),
                new Button("@form.form.formMenu.changeTitle"),
                new Button("@form.form.formMenu.editContent"),
                new Button("@form.form.formMenu.modal.button1"),
                new Button("@form.form.formMenu.modal.button2"),
                new Button("@form.form.formMenu.changeName"),
                new Button("@form.form.recipes"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, ModalForm $form) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("form_menu_prev");
                        is_callable($prev) ? $prev($player) : (new CustomFormForm())->sendMenu($player);
                        break;
                    case 1:
                        (new CustomFormForm())->previewForm($player, $form);
                        break;
                    case 2:
                        (new CustomFormForm())->executeForm($player, $form);
                        break;
                    case 3:
                        (new CustomFormForm())->sendChangeFormTitle($player, $form);
                        break;
                    case 4:
                        (new CustomFormForm())->sendChangeFormContent($player, $form);
                        break;
                    case 5:
                        $this->sendEditButton($player, $form, 1);
                        break;
                    case 6:
                        $this->sendEditButton($player, $form, 2);
                        break;
                    case 7:
                        (new CustomFormForm())->sendChangeFormName($player, $form);
                        break;
                    case 8:
                        (new CustomFormForm())->sendRecipeList($player, $form);
                        break;
                    case 9:
                        (new CustomFormForm())->sendConfirmDelete($player, $form);
                        break;
                }
            })->addArgs($form)->addMessages($messages)->show($player);
    }

    public function sendEditButton(Player $player, ModalForm $form, int $index): void {
        (new CustomForm("@form.form.formMenu.modal.button".$index))
            ->setContents([
                new Label(Language::get("customForm.receive", ["true"])."\n".
                    Language::get("customForm.receive.modal.button", [$index])."\n".
                    Language::get("customForm.receive.modal.button.text", [$index, $form->getButtonText($index)])),
                new Input("@customForm.text", "", $form->getButtonText($index)),
                new CancelToggle(fn() => $this->sendMenu($player, $form, ["@form.cancelled"])),
            ])->onReceive(function (Player $player, array $data) use($form, $index) {
                $form->setButton($index, $data[1]);
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendMenu($player, $form, ["@form.changed"]);
            })->show($player);
    }

}