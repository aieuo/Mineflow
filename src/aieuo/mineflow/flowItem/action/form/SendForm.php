<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;

class SendForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_FORM;

    protected $name = "action.sendForm.name";
    protected $detail = "action.sendForm.detail";
    protected $detailDefaultReplace = ["player", "form"];

    protected $category = Category::FORM;

    /** @var string */
    private $formName;

    public function __construct(string $player = "", string $formName = "") {
        $this->setPlayerVariableName($player);
        $this->formName = $formName;
    }

    public function setFormName(string $formName): void {
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getFormName());
        $manager = Main::getFormManager();
        $helper = Main::getVariableHelper();
        $form = $manager->getForm($name);
        if ($form === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.sendForm.notFound", [$this->getName()]));
        }

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $form = clone $form;
        $form->setTitle($source->replaceVariables($form->getTitle()));
        if ($form instanceof ModalForm) {
            $form->setContent($source->replaceVariables($form->getContent()));
            $form->setButton1($source->replaceVariables($form->getButton1Text()));
            $form->setButton2($source->replaceVariables($form->getButton2Text()));
        } elseif ($form instanceof ListForm) {
            $form->setContent($source->replaceVariables($form->getContent()));
            $buttons = [];
            foreach ($form->getButtons() as $button) {
                if ($helper->isVariableString($button->getText())) {
                    $variableName = substr($button->getText(), 1, -1);
                    $variable = $source->getVariable($variableName) ?? $helper->getNested($variableName);
                    if ($variable instanceof ListVariable) {
                        foreach ($variable->getValue() as $value) {
                            $buttons[] = new Button((string)$value);
                        }
                        continue;
                    }
                }

                $buttons[] = $button->setText($source->replaceVariables($button->getText()));
            }
            $form->setButtons($buttons);
        } elseif ($form instanceof CustomForm) {
            $contents = $form->getContents();
            foreach ($contents as $content) {
                $content->setText($source->replaceVariables($content->getText()));
                if ($content instanceof Input) {
                    $content->setPlaceholder($source->replaceVariables($content->getPlaceholder()));
                    $content->setDefault($source->replaceVariables($content->getDefault()));
                } elseif ($content instanceof Dropdown) {
                    $options = [];
                    foreach ($content->getOptions() as $option) {
                        if ($helper->isVariableString($option)) {
                            $variableName = substr($option, 1, -1);
                            $variable = $source->getVariable($variableName) ?? $helper->getNested($variableName);
                            if ($variable instanceof ListVariable) {
                                foreach ($variable->getValue() as $value) {
                                    $options[] = $source->replaceVariables($value);
                                }
                            }
                        } else {
                            $options[] = $source->replaceVariables($option);
                        }
                    }
                    $content->setOptions($options);
                }
            }
        }
        $form->onReceive([new CustomFormForm(), "onReceive"])->onClose([new CustomFormForm(), "onClose"])->addArgs($form)->show($player);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.sendForm.form.name", "aieuo", $this->getFormName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setFormName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getFormName()];
    }
}