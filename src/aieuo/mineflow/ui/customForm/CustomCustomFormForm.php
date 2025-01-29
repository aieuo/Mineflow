<?php

namespace aieuo\mineflow\ui\customForm;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\NumberInputPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\SliderPlaceholder;
use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\StepSlider;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;

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
                        (new CustomFormForm())->previewForm($player, $form);
                        return;
                    case 2:
                        (new CustomFormForm())->executeForm($player, $form);
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
            ->addButton(new Button("@form.back", fn() => $this->sendMenu($player, $form)))
            ->addButton(new Button("@customForm.custom.element.add", fn() => $this->sendAddElement($player, $form)))
            ->addButtonsEach($form->getContents(), fn(Element $element) =>
                new Button(
                    Language::get("customForm.custom.element", [["customForm.".$element->getType(), [""]], $element->getText()]),
                    fn() => $this->sendEditElement($player, $form, $element)
                )
            )->addMessages($messages)->show($player);
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
                new CancelToggle(fn() => $this->sendElementList($player, $form))
            ])->onReceive(function (Player $player, array $data, CustomForm $form) {
                $element = match ($data[0]) {
                    0 => new Label($data[1]),
                    1 => new Input($data[1]),
                    2 => new NumberInputPlaceholder($data[1]),
                    3 => new SliderPlaceholder($data[1], "0", "0"),
                    4 => new StepSlider($data[1]),
                    5 => new Dropdown($data[1]),
                    6 => new Toggle($data[1]),
                    7 => new CancelToggle(null, $data[1]),
                    default => throw new InvalidFormValueException("@form.insufficient", 0),
                };
                $form->addContent($element);
                Mineflow::getFormManager()->addForm($form->getName(), $form);
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
            case $element instanceof NumberInputPlaceholder:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new Input("@customForm.default", "", $element->getDefault());
                $contents[] = new Toggle("@customForm.input.required", $element->isRequired());
                $contents[] = new Input("@customForm.numberInput.min", "", $element->getMinStr() ?? "");
                $contents[] = new Input("@customForm.numberInput.max", "", $element->getMaxStr() ?? "");
                break;
            case $element instanceof NumberInput:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new Input("@customForm.default", "", $element->getDefault());
                $contents[] = new Toggle("@customForm.input.required", $element->isRequired());
                $contents[] = new Input("@customForm.numberInput.min", "", (string)$element->getMin());
                $contents[] = new Input("@customForm.numberInput.max", "", (string)$element->getMax());
                break;
            case $element instanceof Input:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new Input("@customForm.default", "", $element->getDefault());
                $contents[] = new Toggle("@customForm.input.required", $element->isRequired());
                break;
            case $element instanceof SliderPlaceholder:
                array_unshift($messages, Language::get("customForm.receive.custom.slider", [$index]));
                $contents[] = new Input("@customForm.slider.min", "", $element->getMinStr(), true);
                $contents[] = new Input("@customForm.slider.max", "", $element->getMaxStr(), true);
                $contents[] = new Input("@customForm.slider.step", "", $element->getStepStr(), true);
                $contents[] = new Input("@customForm.default", "", $element->getDefaultStr(), true);
                break;
            case $element instanceof Slider:
                array_unshift($messages, Language::get("customForm.receive.custom.slider", [$index]));
                $contents[] = new Input("@customForm.slider.min", "", $element->getMin(), true);
                $contents[] = new Input("@customForm.slider.max", "", $element->getMax(), true);
                $contents[] = new Input("@customForm.slider.step", "", $element->getStep(), true);
                $contents[] = new Input("@customForm.default", "", $element->getDefault(), true);
                break;
            case $element instanceof Dropdown:
                $dropdown = array_search($element, array_values(array_filter($form->getContents(), fn(Element $element) => $element instanceof Dropdown)), true);
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
                    Mineflow::getFormManager()->addForm($form->getName(), $form);
                    $this->sendElementList($player, $form, ["@form.deleted"]);
                    return;
                }
                $element->setText(array_shift($data));

                switch (true) {
                    case $element instanceof Toggle:
                        $element->setDefault($data[0]);
                        break;
                    case $element instanceof NumberInput:
                        if (!($element instanceof NumberInputPlaceholder)) {
                            $element = new NumberInputPlaceholder(
                                $element->getText(), $element->getPlaceholder(), $element->getDefault(),
                                (string)$element->getMin(), (string)$element->getMax(), $element->getExcludes()
                            );
                        }
                        $element->setPlaceholder($data[0]);
                        $element->setDefault($data[1]);
                        $element->setRequired($data[2]);
                        $element->setMinStr($data[3] === "" ? null : $data[3]);
                        $element->setMaxStr($data[4] === "" ? null : $data[4]);
                        break;
                    case $element instanceof Input:
                        $element->setPlaceholder($data[0]);
                        $element->setDefault($data[1]);
                        $element->setRequired($data[2]);
                        break;
                    case $element instanceof Slider:
                        if (!($element instanceof SliderPlaceholder)) {
                            $element = new SliderPlaceholder(
                                $element->getText(), (string)$element->getMin(), (string)$element->getMax(), (string)$element->getStep(), (string)$element->getDefault()
                            );
                        }
                        $element->setMinStr($data[0]);
                        $element->setMaxStr($data[1]);
                        $element->setStepStr($data[2]);
                        $element->setDefaultStr($data[3]);
                        break;
                    case $element instanceof Dropdown:
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
                Mineflow::getFormManager()->addForm($form->getName(), $form);
                $this->sendElementList($player, $form, ["@form.changed"]);
            })->addMessages($messages)->show($player);
    }

}