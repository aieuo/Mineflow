<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\ui\CustomFormForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\ListVariable;

class SendForm extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_FORM;

    protected $name = "action.sendForm.name";
    protected $detail = "action.sendForm.detail";
    protected $detailDefaultReplace = ["player", "form"];

    protected $category = Category::FORM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $formName;

    public function __construct(string $player = "target", string $formName = "") {
        $this->setPlayerVariableName($player);
        $this->formName = $formName;
    }

    public function setFormName(string $formName) {
        $this->formName = $formName;
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function isDataValid(): bool {
        return $this->formName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getFormName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getFormName());
        $manager = Main::getFormManager();
        $helper = Main::getVariableHelper();
        $form = $manager->getForm($name);
        if ($form === null) {
            throw new \UnexpectedValueException(Language::get("action.sendForm.notFound", [$this->getName()]));
        }

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $form = clone $form;
        $form->setTitle($origin->replaceVariables($form->getTitle()));
        if ($form instanceof ModalForm) {
            $form->setContent($origin->replaceVariables($form->getContent()));
            $form->setButton1($origin->replaceVariables($form->getButton1()));
            $form->setButton2($origin->replaceVariables($form->getButton2()));
        } elseif ($form instanceof ListForm) {
            $form->setContent($origin->replaceVariables($form->getContent()));
            $buttons = [];
            foreach ($form->getButtons() as $button) {
                if ($helper->isVariableString($button->getText())) {
                    $variableName = substr($button->getText(), 1, -1);
                    $variable = $origin->getVariable($variableName) ?? $helper->getNested($variableName);
                    if ($variable instanceof ListVariable) {
                        foreach ($variable->getValue() as $value) {
                            $buttons[] = new Button((string)$value);
                        }
                    }
                } else {
                    $buttons[] = new Button($origin->replaceVariables($button->getText()));
                }
            }
            $form->setButtons($buttons);
        } elseif ($form instanceof CustomForm) {
            $contents = $form->getContents();
            foreach ($contents as $content) {
                $content->setText($origin->replaceVariables($content->getText()));
                if ($content instanceof Input) {
                    $content->setPlaceholder($origin->replaceVariables($content->getPlaceholder()));
                    $content->setDefault($origin->replaceVariables($content->getDefault()));
                } elseif ($content instanceof Dropdown) {
                    $options = [];
                    foreach ($content->getOptions() as $option) {
                        if ($helper->isVariableString($option)) {
                            $variableName = substr($option, 1, -1);
                            $variable = $origin->getVariable($variableName) ?? $helper->getNested($variableName);
                            if ($variable instanceof ListVariable) {
                                foreach ($variable->getValue() as $value) {
                                    $options[] = $origin->replaceVariables($value);
                                }
                            }
                        } else {
                            $options[] = $origin->replaceVariables($option);
                        }
                    }
                    $content->setOptions($options);
                }
            }
        }
        $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form)->show($player);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.sendForm.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getFormName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();

        $this->setPlayerVariableName($content[0]);
        $this->setFormName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getFormName()];
    }
}