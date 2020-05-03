<?php


namespace aieuo\mineflow\ui;


use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\StepSlider;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DefaultVariables;
use pocketmine\Player;

class CustomFormForm {

    public function sendMenu(Player $player, array $messages = []) {
        (new ListForm("@form.form.menu.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.add"),
                new Button("@form.edit"),
                new Button("@form.form.menu.formList"),
            ])->onReceive(function (Player $player, int $data) {
                switch ($data) {
                    case 0:
                        (new HomeForm)->sendMenu($player);
                        break;
                    case 1:
                        $this->sendAddForm($player);
                        break;
                    case 2:
                        $this->sendSelectForm($player);
                        break;
                    case 3:
                        $this->sendFormList($player);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendAddForm(Player $player, array $defaults = [], array $errors = []) {
        (new CustomForm("@form.form.addForm.title"))
            ->setContents([
                new Input("@customForm.name", "", $defaults[0] ?? ""),
                new Dropdown("@form.form.addForm.type", [
                    Language::get("customForm.modal"),
                    Language::get("customForm.form"),
                    Language::get("customForm.custom_form"),
                ]),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data) {
                if ($data[2]) {
                    $this->sendMenu($player);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendAddForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }

                switch ($data[1]) {
                    case 0:
                        $form = new ModalForm($data[0]);
                        break;
                    case 1:
                        $form = new ListForm($data[0]);
                        break;
                    case 2:
                        $form = new CustomForm($data[0]);
                        break;
                    default:
                        $this->sendAddForm($player, $data, [["@form.insufficient", 1]]);
                        return;
                }

                $manager = Main::getFormManager();
                if ($manager->existsForm($data[0])) {
                    $newName = $manager->getNotDuplicatedName($data[0]);
                    (new MineflowForm)->confirmRename($player, $data[0], $newName,
                        function (Player $player, string $name) use ($form) {
                            $manager = Main::getFormManager();
                            $form->setTitle($name);
                            $manager->addForm($name, $form);
                            Session::getSession($player)->set("form_menu_prev", [$this, "sendMenu"]);
                            $this->sendFormMenu($player, $form);
                        },
                        function (Player $player, string $name) use ($data) {
                            $this->sendAddForm($player, $data, [[Language::get("form.form.exists", [$name]), 0]]);
                        });
                    return;
                }
                $manager->addForm($data[0], $form);
                Session::getSession($player)->set("form_menu_prev", [$this, "sendMenu"]);
                $this->sendFormMenu($player, $form);
            })->addErrors($errors)->show($player);
    }

    public function sendSelectForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@form.form.select.title"))
            ->setContents([
                new Input("@customForm.name", "", $default[0] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    $this->sendMenu($player);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSelectForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getFormManager();
                $name = $data[0];
                if (!$manager->existsForm($name)) {
                    $this->sendSelectForm($player, $data, [["@form.form.notFound", 0]]);
                    return;
                }

                $form = $manager->getForm($name);
                Session::getSession($player)->set("form_menu_prev", [$this, "sendSelectForm"]);
                $this->sendFormMenu($player, $form);
        })->addErrors($errors)->show($player);
    }

    public function sendFormList(Player $player) {
        $manager = Main::getFormManager();
        $forms = $manager->getAllFormData();
        $buttons = [new Button("@form.back")];
        foreach ($forms as $form) {
            $buttons[] = new Button($form["name"].": ".Language::get("customForm.".$form["type"]));
        }

        (new ListForm("@form.form.menu.formList"))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, array $forms) {
                if ($data === 0) {
                    $this->sendMenu($player);
                    return;
                }
                $data --;

                $form = $forms[$data]["form"];
                if (!($form instanceof Form)) $form = Form::createFromArray($forms[$data]["form"], $forms[$data]["name"]); // FIXME: error object?
                Session::getSession($player)->set("form_menu_prev", [$this, "sendFormList"]);
                $this->sendFormMenu($player, $form);
            })->addArgs(array_values($forms))->show($player);
    }

    public function sendFormMenu(Player $player, Form $form, array $messages = []) {
        switch ($form->getType()) {
            case Form::MODAL_FORM:
                if (!($form instanceof ModalForm)) return;
                $this->sendModalFormMenu($player, $form, $messages);
                break;
            case Form::LIST_FORM:
                if (!($form instanceof ListForm)) return;
                $this->sendListFormMenu($player, $form, $messages);
                break;
            case Form::CUSTOM_FORM:
                if (!($form instanceof CustomForm)) return;
                $this->sendCustomFormMenu($player, $form, $messages);
                break;
        }
    }

    public function sendModalFormMenu(Player $player, ModalForm $form, array $messages = []) {
        (new ListForm(Language::get($form->getName())))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.form.formMenu.preview"),
                new Button("@form.recipe.recipeMenu.execute"),
                new Button("@form.form.formMenu.changeTitle"),
                new Button("@form.form.formMenu.content"),
                new Button("@form.form.formMenu.modal.button1"),
                new Button("@form.form.formMenu.modal.button2"),
                new Button("@form.form.formMenu.changeName"),
                new Button("@form.form.recipes"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, ModalForm $form) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("form_menu_prev");
                        if (is_callable($prev)) call_user_func_array($prev, [$player]);
                        else $this->sendMenu($player);
                        break;
                    case 1:
                        $form->onReceive(function (Player $player) use ($form) {
                            $this->sendModalFormMenu($player, $form);
                        })->onClose(function (Player $player) use ($form) {
                            $this->sendModalFormMenu($player, $form);
                        })->show($player);
                        break;
                    case 2:
                        $form->onReceive([new CustomFormForm(), "onReceive"])
                            ->onClose([new CustomFormForm(), "onClose"])
                            ->addArgs($form)->show($player);
                        break;
                    case 3:
                        $this->sendChangeFormTitle($player, $form);
                        break;
                    case 4:
                        $this->sendChangeFormContent($player, $form);
                        break;
                    case 5:
                        (new CustomForm("@form.form.formMenu.modal.button1"))
                            ->setContents([
                                new Label(Language::get("customForm.receive", ["true"])."\n".
                                    Language::get("customForm.receive.modal.button", ["1"])."\n".
                                    Language::get("customForm.receive.modal.button.text", ["1", $form->getButton1()])),
                                new Input("@customForm.text", "", $form->getButton1()),
                                new Toggle("@form.cancelAndBack"),
                            ])->onReceive(function (Player $player, array $data, ModalForm $form) {
                                if ($data[2]) {
                                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                                    return;
                                }

                                $form->setButton1($data[1]);
                                Main::getFormManager()->addForm($form->getName(), $form);
                                $this->sendFormMenu($player, $form, ["@form.changed"]);
                            })->addArgs($form)->show($player);
                        break;
                    case 6:
                        (new CustomForm("@form.form.formMenu.modal.button2"))
                            ->setContents([
                                new Label(Language::get("customForm.receive", ["false"])."\n".
                                    Language::get("customForm.receive.modal.button", ["2"])."\n".
                                    Language::get("customForm.receive.modal.button.text", ["2", $form->getButton2()])),
                                new Input("@customForm.text", "", $form->getButton2()),
                                new Toggle("@form.cancelAndBack"),
                            ])->onReceive(function (Player $player, array $data, ModalForm $form) {
                                if ($data[0]) {
                                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                                    return;
                                }

                                $form->setButton2($data[1]);
                                Main::getFormManager()->addForm($form->getName(), $form);
                                $this->sendFormMenu($player, $form, ["@form.changed"]);
                            })->addArgs($form)->show($player);
                        break;
                    case 7:
                        $this->sendChangeFormName($player, $form);
                        break;
                    case 8:
                        $this->sendRecipeList($player, $form);
                        break;
                    case 9:
                        $this->sendConfirmDelete($player, $form);
                        break;
                }
            })->addArgs($form)->addMessages($messages)->show($player);
    }

    public function sendListFormMenu(Player $player, ListForm $form, array $messages = []) {
        $buttons = [
            new Button("@form.back"),
            new Button("@form.form.formMenu.preview"),
            new Button("@form.recipe.recipeMenu.execute"),
            new Button("@form.form.formMenu.changeTitle"),
            new Button("@form.form.formMenu.content"),
        ];
        $formButtons = $form->getButtons();
        foreach ($formButtons as $button) {
            $buttons[] = new Button(Language::get("form.form.formMenu.list.button", [$button->getText()]));
        }
        $buttons[] = new Button("@customForm.list.addButton");
        $buttons[] = new Button("@form.form.formMenu.changeName");
        $buttons[] = new Button("@form.form.recipes");
        $buttons[] = new Button("@form.delete");
        (new ListForm(Language::get("form.form.formMenu.changeTitle", [$form->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, ListForm $form, array $buttons) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("form_menu_prev");
                        if (is_callable($prev)) call_user_func_array($prev, [$player]);
                        else $this->sendMenu($player);
                        return;
                    case 1:
                        $form->onReceive(function (Player $player) use ($form) {
                            $this->sendListFormMenu($player, $form);
                        })->onClose(function (Player $player) use ($form) {
                            $this->sendListFormMenu($player, $form);
                        })->show($player);
                        return;
                    case 2:
                        $form->onReceive([new CustomFormForm(), "onReceive"])
                            ->onClose([new CustomFormForm(), "onClose"])
                            ->addArgs($form)->show($player);
                        return;
                    case 3:
                        $this->sendChangeFormTitle($player, $form);
                        return;
                    case 4:
                        $this->sendChangeFormContent($player, $form);
                        return;
                }
                $data -= 5;

                switch ($data - count($buttons)) {
                    case 0:
                        $this->sendAddListButton($player, $form);
                        return;
                    case 1:
                        $this->sendChangeFormName($player, $form);
                        return;
                    case 2:
                        $this->sendRecipeList($player, $form);
                        return;
                    case 3:
                        $this->sendConfirmDelete($player, $form);
                        return;
                }

                /** @var Button $button */
                $button = $buttons[$data];
                (new CustomForm($button->getText()))
                    ->setContents([
                        new Label(Language::get("customForm.receive", [$data])."\n".
                            Language::get("customForm.receive.list.button", [$button->getText()])),
                        new Input("@customForm.list.editButton", "", $button->getText()),
                    ])->onReceive(function (Player $player, array $data, ListForm $form, int $selected, array $buttons) {
                        /** @var Button $button */
                        $button = $buttons[$selected];
                        if ($data[1] === "") {
                            unset($buttons[$selected]);
                        } else {
                            $buttons[$selected] = $button->setText($data[1]);
                        }
                        $buttons = array_values($buttons);
                        $form->setButtons($buttons);
                        Main::getFormManager()->addForm($form->getName(), $form);
                        $this->sendFormMenu($player, $form, ["@form.changed"]);
                    })->addArgs($form, $data, $buttons)->show($player);
            })->addArgs($form, $form->getButtons())->addMessages($messages)->show($player);
    }

    public function sendCustomFormMenu(Player $player, CustomForm $form, array $messages = []) {
        $buttons = [
            new Button("@form.back"),
            new Button("@form.form.formMenu.preview"),
            new Button("@form.recipe.recipeMenu.execute"),
            new Button("@form.form.formMenu.changeTitle"),
        ];
        $elements = $form->getContents();
        foreach ($elements as $element) {
            $buttons[] = new Button(Language::get("customForm.custom.element", [["customForm.".$element->getType(), [""]], $element->getText()]));
        }
        $buttons[] = new Button("@customForm.custom.element.add");
        $buttons[] = new Button("@form.form.formMenu.changeName");
        $buttons[] = new Button("@form.form.recipes");
        $buttons[] = new Button("@form.delete");
        (new ListForm(Language::get("form.form.formMenu.changeTitle", [$form->getName()])))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, CustomForm $form, array $elements) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("form_menu_prev");
                        if (is_callable($prev)) call_user_func_array($prev, [$player]);
                        else $this->sendMenu($player);
                        return;
                    case 1:
                        $form->onReceive(function (Player $player) use ($form) {
                            $this->sendCustomFormMenu($player, $form);
                        })->onClose(function (Player $player) use ($form) {
                            $this->sendCustomFormMenu($player, $form);
                        })->show($player);
                        return;
                    case 2:
                        $form->onReceive([new CustomFormForm(), "onReceive"])
                            ->onClose([new CustomFormForm(), "onClose"])
                            ->addArgs($form)->show($player);
                        return;
                    case 3:
                        $this->sendChangeFormTitle($player, $form);
                        return;
                }
                $data -= 4;

                switch ($data - count($elements)) {
                    case 0:
                        $this->sendAddCustomFormElement($player, $form);
                        return;
                    case 1:
                        $this->sendChangeFormName($player, $form);
                        return;
                    case 2:
                        $this->sendRecipeList($player, $form);
                        return;
                    case 3:
                        $this->sendConfirmDelete($player, $form);
                        return;
                }

                /** @var Element $element */
                $element = $elements[$data];
                $this->sendCustomFormElementMenu($player, $form, $element);
            })->addArgs($form, $form->getContents())->addMessages($messages)->show($player);
    }

    public function sendChangeFormTitle(Player $player, Form $form) {
        (new CustomForm("@form.form.formMenu.changeTitle"))
            ->setContents([
                new Input("@customForm.title", "", $form->getTitle()),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data, Form $form) {
                if ($data[1]) {
                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                    return;
                }

                $form->setTitle($data[0]);
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendFormMenu($player, $form, ["@form.changed"]);
            })->addArgs($form)->show($player);
    }

    public function sendChangeFormContent(Player $player, Form $form) {
        if (!($form instanceof ModalForm) and !($form instanceof ListForm)) return;
        (new CustomForm("@form.form.formMenu.content"))
            ->setContents([
                new Input("@customForm.content", "", $form->getContent()),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data, Form $form) {
                if (!($form instanceof ModalForm) and !($form instanceof ListForm)) return;

                if ($data[1]) {
                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                    return;
                }

                $form->setContent($data[0]);
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendFormMenu($player, $form, ["@form.changed"]);
            })->addArgs($form)->show($player);
    }

    public function sendChangeFormName(Player $player, Form $form, array $default = [], array $errors = []) {
        (new CustomForm("@form.form.formMenu.changeName"))
            ->setContents([
                new Input("@customForm.name", "", $default[0] ?? $form->getName()),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data, Form $form) {
                if ($data[1]) {
                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendChangeFormName($player, $form, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getFormManager();
                if ($manager->existsForm($data[0])) {
                    $newName = $manager->getNotDuplicatedName($data[0]);
                    (new MineflowForm)->confirmRename($player, $data[0], $newName,
                        function (Player $player, string $name) use ($form) {
                            $form->setName($name);
                            $manager = Main::getFormManager();
                            $manager->removeForm($name);
                            $manager->addForm($name, $form);
                            $this->sendFormMenu($player, $form, ["@form.changed"]);
                        },
                        function (Player $player, string $name) use ($form, $data) {
                            $this->sendChangeFormName($player, $form, $data, [[Language::get("customForm.exists", [$name]), 0]]);
                        });
                    return;
                }

                $manager->removeForm($form->getName());
                $form->setName($data[0]);
                $manager->addForm($data[0], $form);
                $this->sendFormMenu($player, $form, ["@form.changed"]);
            })->addArgs($form)->addErrors($errors)->show($player);
    }

    public function sendAddListButton(Player $player, ListForm $form, array $errors = []) {
        (new CustomForm("@customForm.list.addButton"))
            ->setContents([
                new Input("@customForm.text"),
            ])->onReceive(function (Player $player, array $data, ListForm $form) {
                if ($data[0] === "") {
                    $this->sendAddListButton($player, $form, [["@form.insufficient", 0]]);
                    return;
                }

                $form->addButton(new Button($data[0]));
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendListFormMenu($player, $form, ["@form.added"]);
            })->addArgs($form)->addErrors($errors)->show($player);
    }

    public function sendAddCustomFormElement(Player $player, CustomForm $form) {
        (new CustomForm("@customForm.custom.element.add"))
            ->setContents([
                new Dropdown("@customForm.custom.element.select", [
                    Language::get("customForm.label", [" (label)"]),
                    Language::get("customForm.input", [" (input)"]),
                    Language::get("customForm.slider", [" (slider)"]),
                    Language::get("customForm.step_slider", [" (step_slider)"]),
                    Language::get("customForm.dropdown", [" (dropdown)"]),
                    Language::get("customForm.toggle", [" (toggle)"]),
                ]),
                new Input("@customForm.text"),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, array $data, CustomForm $form) {
                if ($data[2]) {
                    $this->sendFormMenu($player, $form);
                    return;
                }

                switch ($data[0]) {
                    case 0:
                        $element = new Label($data[1]);
                        break;
                    case 1:
                        $element = new Input($data[1]);
                        break;
                    case 2:
                        $element = new Slider($data[1], 0, 0);
                        break;
                    case 3:
                        $element = new StepSlider($data[1]);
                        break;
                    case 4:
                        $element = new Dropdown($data[1]);
                        break;
                    case 5:
                        $element = new Toggle($data[1]);
                        break;
                    default:
                        return;
                }
                $form->addContent($element);
                Main::getFormManager()->addForm($form->getName(), $form);
                $this->sendCustomFormElementMenu($player, $form, $element, ["@form.added"]);
            })->addArgs($form)->show($player);
    }

    public function sendCustomFormElementMenu(Player $player, CustomForm $form, Element $element, array $messages = []) {
        $contents = [
            new Input("@customForm.text", "", $element->getText())
        ];
        $index = array_search($element, $form->getContents(), true);
        switch ($element) {
            case $element instanceof Toggle:
                array_unshift($messages, Language::get("customForm.receive.custom", [$index, "(true | false)"]));
                $contents[] = new Toggle("@customForm.default", $element->getDefault());
                break;
            case $element instanceof Label:
                array_unshift($messages, Language::get("customForm.receive.custom", [$index, ""]));
                break;
            case $element instanceof Input:
                array_unshift($messages, Language::get("customForm.receive.custom.input", [$index]));
                $contents[] = new Input("@customForm.input.placeholder", "", $element->getPlaceholder());
                $contents[] = new Input("@customForm.default", "", $element->getDefault());
                break;
            case $element instanceof Slider:
                array_unshift($messages, Language::get("customForm.receive.custom.slider", [$index]));
                $contents[] = new Input("@customForm.slider.min", "", (string)$element->getMin());
                $contents[] = new Input("@customForm.slider.max", "", (string)$element->getMax());
                $contents[] = new Input("@customForm.slider.step", "", (string)$element->getStep());
                $contents[] = new Input("@customForm.default", "", (string)$element->getDefault());
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
            ->onReceive(function (Player $player, array $data, CustomForm $form, Element $element) {
                $elements = $form->getContents();
                $index = array_search($element, $elements, true);

                $element->setText($data[0]);
                $delete = false;
                switch ($element) {
                    case $element instanceof Toggle:
                        $element->setDefault($data[1]);
                        $delete = $data[2];
                        break;
                    case $element instanceof Input:
                        $element->setPlaceholder($data[1]);
                        $element->setDefault($data[2]);
                        $delete = $data[3];
                        break;
                    case $element instanceof Slider:
                        $element->setMin((float)$data[1]);
                        $element->setMax((float)$data[2]);
                        $element->setStep((float)$data[3]);
                        $element->setDefault((float)$data[4]);
                        $delete = $data[5];
                        break;
                    case $element instanceof Dropdown:
                    case $element instanceof StepSlider:
                        $options = [];
                        $i = -1;
                        foreach ($element->getOptions() as $i => $option) {
                            if ($data[$i+1] !== "") $options[] = $data[$i+1];
                        }
                        foreach (explode(";", $data[$i+2]) as $item) {
                            if ($item !== "") $options[] = $item;
                        }
                        $element->setOptions($options);
                        $delete = $data[$i+3];
                        break;
                }
                if ($delete) {
                    unset($elements[$index]);
                    $elements = array_values($elements);
                    $form->setContents($elements);
                    Main::getFormManager()->addForm($form->getName(), $form);
                    $this->sendCustomFormMenu($player, $form, ["@form.delete.success"]);
                } else {
                    $elements[$index] = $element;
                    $form->setContents($elements);
                    Main::getFormManager()->addForm($form->getName(), $form);
                    $this->sendCustomFormMenu($player, $form, ["@form.changed"]);
                }
            })->addArgs($form, $element)->addMessages($messages)->show($player);

    }

    public function sendRecipeList(Player $player, Form $form, array $messages = []) {
        $buttons = [new Button("@form.back"), new Button("@form.add")];

        $recipes = Main::getFormManager()->getAssignedRecipes($form->getName());
        foreach ($recipes as $name => $keys) {
            $buttons[] = new Button($name." | ".count($keys));
        }
        (new ListForm(Language::get("form.recipes.title", [$form->getName()])))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, Form $form, array $recipes) {
                switch ($data) {
                    case 0:
                        $this->sendFormMenu($player, $form);
                        return;
                    case 1:
                        $this->sendSelectRecipe($player, $form);
                        return;
                }
                $data -= 2;

                $this->sendRecipeMenu($player, $form, $data, $recipes);
            })->addMessages($messages)->addArgs($form, $recipes)->show($player);
    }

    public function sendSelectRecipe(Player $player, Form $form, array $default = [], array $errors = []) {
        (new CustomForm(Language::get("form.recipes.add", [$form->getName()])))
            ->setContents([
                new Input("@form.recipe.recipeName", "", $default[0] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onReceive(function (Player $player, array $data, Form $form) {
                if ($data[1]) {
                    $this->sendRecipeList($player, $form);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSelectRecipe($player, $form, $data, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getRecipeManager();
                [$name, $group] = $manager->parseName($data[0]);
                $recipe = $manager->get($name, $group);
                if ($recipe === null) {
                    $this->sendSelectRecipe($player, $form, $data, [["@form.recipe.select.notfound", 0]]);
                    return;
                }

                $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendRecipeList($player, $form, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendRecipeList($player, $form, ["@form.added"]);
            })->addArgs($form)->addErrors($errors)->show($player);
    }

    public function sendRecipeMenu(Player $player, Form $form, int $index, array $recipes) {
        $triggers = array_values($recipes)[$index];
        $content = implode("\n", array_map(function (string $key) use ($form) {
            switch ($key) {
                case "":
                    return Language::get("trigger.form.receive");
                case "close":
                    return Language::get("trigger.form.close");
                default:
                    if ($form instanceof ListForm) {
                        $button = $form->getButtonById($key);
                        return Language::get("trigger.form.button", [$button instanceof Button ? $button->getType() : ""]);
                    } else {
                        return "";
                    }
            }
        }, $triggers));
        (new ListForm(Language::get("form.recipes.title", [$form->getName()])))
            ->setContent($content)
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.edit")
            ])->onReceive(function (Player $player, int $data, Form $form, int $index, array $recipes) {
                if ($data === 0) {
                    $this->sendRecipeList($player, $form);
                } elseif ($data === 1) {
                    Session::getSession($player)
                        ->set("recipe_menu_prev", [$this, "sendRecipeMenu"])
                        ->set("recipe_menu_prev_data", [$form, $index, $recipes]);
                    $recipeName = array_keys($recipes)[$index];
                    [$name, $group] = Main::getRecipeManager()->parseName($recipeName);
                    $recipe = Main::getRecipeManager()->get($name, $group);
                    (new RecipeForm())->sendTriggerList($player, $recipe);
                }
            })->addArgs($form, $index, $recipes)->show($player);
    }

    public function sendConfirmDelete(Player $player, Form $form) {
        (new ModalForm(Language::get("form.recipe.delete.title", [$form->getName()])))
            ->setContent(Language::get("form.delete.confirm", [$form->getName()]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, Form $form) {
                if ($data) {
                    $manager = Main::getFormManager();
                    $manager->removeForm($form->getName());
                    $this->sendMenu($player, ["@form.delete.success"]);
                } else {
                    $this->sendFormMenu($player, $form, ["@form.cancelled"]);
                }
            })->addArgs($form)->show($player);
    }

    public function onReceive(Player $player, $data, Form $form) {
        $holder = TriggerHolder::getInstance();
        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
        $variables = Main::getFormManager()->getFormDataVariable($form, $data);
        if ($holder->existsRecipe($trigger)) {
            $recipes = $holder->getRecipes($trigger);
            $recipes->executeAll($player, $variables);
        }
        switch ($form) {
            case $form instanceof ModalForm:
                /** @var bool $data */
                $trigger->setSubKey($data ? "1" : "2");
                if ($holder->existsRecipe($trigger)) {
                    $recipes = $holder->getRecipes($trigger);
                    $recipes->executeAll($player, $variables);
                }
                break;
            case $form instanceof ListForm:
                /** @var int $data */
                $button = $form->getButton($data);
                $trigger->setSubKey($button->getUUId());
                if ($holder->existsRecipe($trigger)) {
                    $recipes = $holder->getRecipes($trigger);
                    $recipes->executeAll($player, $variables);
                }
                break;
        }
    }

    public function onClose(Player $player, Form $form) {
        $holder = TriggerHolder::getInstance();
        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName(), "close");
        if ($holder->existsRecipe($trigger)) {
            $recipes = $holder->getRecipes($trigger);
            $recipes->executeAll($player);
        }
    }
}
