<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\StepSlider;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\Player;

class CustomCustomFormForm {

    public function sendMenu(Player $player, CustomForm $form, array $messages = []): void {
        (new ListForm(Language::get("form.form.formMenu.changeTitle", [$form->getName()])))
            ->addButton(new Button("@form.back"))
            ->addButton(new Button("@form.form.formMenu.preview"))
            ->addButton(new Button("@form.recipe.recipeMenu.execute"))
            ->addButton(new Button("@form.form.formMenu.changeTitle"))
            ->addButton(new Button("@customForm.custom.element.edit"))
            ->addButton(new Button("@form.form.formMenu.changeName"))
            ->addButton(new Button("@form.form.recipes"))
            ->addButton(new Button("@form.delete"))
            ->onReceive(function (Player $player, int $data) use($form) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("form_menu_prev");
                        is_callable($prev) ? $prev($player) : (new CustomFormForm())->sendMenu($player);
                        return;
                    case 1:
                        (clone $form)->onReceive(function (Player $player) use ($form) {
                            $this->sendMenu($player, $form);
                        })->onClose(function (Player $player) use ($form) {
                            $this->sendMenu($player, $form);
                        })->show($player);
                        return;
                    case 2:
                        (clone $form)->onReceive([new CustomFormForm(), "onReceive"])
                            ->onClose([new CustomFormForm(), "onClose"])
                            ->addArgs($form)->show($player);
                        return;
                    case 3:
                        (new CustomFormForm())->sendChangeFormTitle($player, $form);
                        return;
                    case 4:
                        $this->sendElementList($player, $form);
                        return;
                    case 5:
                        (new CustomFormForm())->sendChangeFormName($player, $form);
                        return;
                    case 6:
                        (new CustomFormForm())->sendRecipeList($player, $form);
                        return;
                    case 7:
                        (new CustomFormForm())->sendConfirmDelete($player, $form);
                        return;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendElementList(Player $player, CustomForm $form, array $messages = []): void {
        (new ListForm("@customForm.custom.element.edit"))
            ->addButton(new Button("@form.back", function () use ($player, $form) { $this->sendMenu($player, $form); }))
            ->addButton(new Button("@customForm.custom.element.add", function () use ($player, $form) { $this->sendAddElement($player, $form); }))
            ->addButtonsEach($form->getContents(), function (Element $element) use($player, $form) {
                return new Button(Language::get("customForm.custom.element", [["customForm.".$element->getType(), [""]], $element->getText()]), function() use($player, $form, $element) {
                    $this->sendEditElement($player, $form, $element);
                });
            })->addMessages($messages)->show($player);
    }

    public function sendAddElement(Player $player, CustomForm $form): void {
        (new CustomForm("@customForm.custom.element.add"))
            ->setContents([
                new Dropdown("@customForm.custom.element.select", [
                    Language::get("customForm.label", [" (label)"]),
                    Language::get("customForm.input", [" (input)"]),
                    Language::get("customForm.numberInput", [" (number input)"]),
                    Language::get("customForm.slider", [" (slider)"]),
                    Language::get("customForm.step_slider", [" (step_slider)"]),
                    Language::get("customForm.dropdown", [" (dropdown)"]),
                    Language::get("customForm.toggle", [" (toggle)"]),
                    Language::get("customForm.cancelToggle", [" (cancel toggle)"]),
                ]),
                new Input("@customForm.text"),
                new CancelToggle(function() use($player, $form) { $this->sendElementList($player, $form); })
            ])->onReceive(function (Player $player, array $data, CustomForm $form) {
                switch ($data[0]) {
                    case 0:
                        $element = new Label($data[1]);
                        break;
                    case 1:
                        $element = new Input($data[1]);
                        break;
                    case 2:
                        $element = new NumberInput($data[1]);
                        break;
                    case 3:
                        $element = new Slider($data[1], 0, 0);
                        break;
                    case 4:
                        $element = new StepSlider($data[1]);
                        break;
                    case 5:
                        $element = new Dropdown($data[1]);
                        break;
                    case 6:
                        $element = new Toggle($data[1]);
                        break;
                    case 7:
                        $element = new CancelToggle(null, $data[1]);
                        break;
                    default:
                        return;
                }
                $form->addContent($element);
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendEditElement($player, $form, $element, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendEditElement(Player $player, CustomForm $form, Element $element, array $messages = []): void {
        $contents = [new Input("@customForm.text", "", $element->getText())];
        $index = array_search($element, $form->getContents(), true);
        switch (true) {
            case $element instanceof CancelToggle:
                array_unshift($messages, Language::get("customForm.receive.custom", [$index, "(true | false)"]));
                array_unshift($messages, Language::get("customForm.cancelToggle.detail"));
                $contents[] = new CancelToggle(null, "@customForm.default", $element->getDefault());
                break;
            case $element instanceof Toggle:
                array_unshift($messages, Language::get("customForm.receive.custom", [$index, "(true | false)"]));
                $contents[] = new Toggle("@customForm.default", $element->getDefault());
                break;
            case $element instanceof Label:
                array_unshift($messages, Language::get("customForm.receive.custom", [$index, ""]));
                break;
            case $element instanceof NumberInput:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new NumberInput("@customForm.default", "", $element->getDefault());
                $contents[] = new Toggle("@customForm.input.required", $element->isRequired());
                $contents[] = new NumberInput("@customForm.numberInput.min", "", (string)$element->getMin());
                $contents[] = new NumberInput("@customForm.numberInput.max", "", (string)$element->getMax());
                break;
            case $element instanceof Input:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new Input("@customForm.default", "", $element->getDefault());
                $contents[] = new Toggle("@customForm.input.required", $element->isRequired());
                break;
            case $element instanceof Slider:
                array_unshift($messages, Language::get("customForm.receive.custom.slider", [$index]));
                $contents[] = new NumberInput("@customForm.slider.min", "", (string)$element->getMin());
                $contents[] = new NumberInput("@customForm.slider.max", "", (string)$element->getMax());
                $contents[] = new NumberInput("@customForm.slider.step", "", (string)$element->getStep());
                $contents[] = new NumberInput("@customForm.default", "", (string)$element->getDefault());
                break;
            case $element instanceof Dropdown:
            case $element instanceof StepSlider:
                $dropdown = array_search($element, array_values(array_filter($form->getContents(), function (Element $element) {
                    return $element instanceof Dropdown;
                })), true);
                array_unshift($messages, Language::get("customForm.receive.custom.dropdown.text", [$dropdown]));
                array_unshift($messages, Language::get("customForm.receive.custom.dropdown", [$index]));
                foreach ($element->getOptions() as $i => $option) {
                    $contents[] = new Input(Language::get("customForm.dropdown.option", [$i]), "", $option);
                }
                $contents[] = new Input("@customForm.dropdown.option.add");
                break;
        }
        $contents[] = new Toggle("@form.delete");

        (new CustomForm("@customForm.custom.element.edit"))
            ->setContents($contents)
            ->onReceive(function (Player $player, array $data) use($form, $element) {
                $elements = $form->getContents();
                $index = array_search($element, $elements, true);

                if (array_pop($data)) {
                    $form->removeContentAt($index);
                    Main::getFormManager()->addForm($form->getName(), $form);
                    $this->sendElementList($player, $form, ["@form.deleted"]);
                    return;
                }
                $element->setText(array_shift($data));

                switch (true) {
                    case $element instanceof Toggle:
                        $element->setDefault($data[0]);
                        break;
                    case $element instanceof NumberInput:
                        $element->setPlaceholder($data[0]);
                        $element->setDefault($data[1]);
                        $element->setRequired($data[2]);
                        $element->setMin($data[3] === "" ? null : (float)$data[3]);
                        $element->setMax($data[4] === "" ? null : (float)$data[4]);
                        break;
                    case $element instanceof Input:
                        $element->setPlaceholder($data[0]);
                        $element->setDefault($data[1]);
                        $element->setRequired($data[2]);
                        break;
                    case $element instanceof Slider:
                        $element->setMin((float)$data[0]);
                        $element->setMax((float)$data[1]);
                        $element->setStep((float)$data[2]);
                        $element->setDefault((float)$data[3]);
                        break;
                    case $element instanceof Dropdown:
                    case $element instanceof StepSlider:
                        $add = [];
                        $options = [];
                        foreach (explode(";", array_pop($data)) as $item) {
                            if ($item !== "") $add[] = $item;
                        }
                        foreach ($data as $option) {
                            if ($option !== "") $options[] = $option;
                        }
                        $element->setOptions(array_merge($options, $add));
                        break;
                }

                $form->setContent($element, $index);
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendElementList($player, $form, ["@form.changed"]);
            })->addMessages($messages)->show($player);
    }

}