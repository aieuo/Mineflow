<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\player\Player;

abstract class VariableDropdown extends Dropdown {

    protected string $variableType;

    private string $defaultText;

    /** @var string[] */
    protected array $actions = [];
    private array $variableTypes;
    private array $variableNames = [];

    private bool $optional;

    private int $createVariableOptionIndex = -1;
    private int $inputManuallyOptionIndex = -1;

    /**
     * @param string $text
     * @param array<string, DummyVariable> $variables
     * @param string[] $variableTypes
     * @param string $default
     * @param bool $optional
     */
    public function __construct(string $text, array $variables = [], array $variableTypes = [], string $default = "", bool $optional = false) {
        $this->defaultText = $default;
        $this->variableTypes = $variableTypes;
        $this->optional = $optional;
        $options = $this->updateOptions($this->flattenVariables($variables));

        parent::__construct($text, $options, $this->findDefaultKey($default));
    }

    public function updateOptions(array $variables): array {
        $variableTypes = $this->variableTypes;
        $default = $this->defaultText;

        $options = [];
        foreach ($variables as $name => $variable) {
            if (!in_array($variable->getValueType(), $variableTypes, true)) continue;

            $options[$name] = empty($variable->getDescription()) ? $name : ($name." §7(".$variable->getDescription().")");
        }

        if ($default !== "" and !isset($options[$default])) {
            $options[$default] = $default;
        }

        if ($this->isOptional()) {
            array_unshift($options, Language::get("form.element.variableDropdown.none"));
        }
        if ($this->canSendCreateVariableForm()) {
            $options[] = Language::get("form.element.variableDropdown.createVariable");
            $this->createVariableOptionIndex = count($options) - 1;
        }
        $options[] = Language::get("form.element.variableDropdown.inputManually");
        $this->inputManuallyOptionIndex = count($options) - 1;

        $this->variableNames = array_keys($options);
        $this->options = array_values($options);
        return $this->options;
    }

    public function findDefaultKey(string $default): int {
        if ($default === "") return 0;

        $key = array_search($default, $this->variableNames, true);
        return $key === false ? 0 : $key;
    }

    public function updateDefault(string $default): void {
        $this->defaultText = $default;
        $this->setDefault($this->findDefaultKey($default));
    }

    public function getVariableType(): string {
        return $this->variableType;
    }

    public function getDefaultText(): string {
        return $this->defaultText;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function canSendCreateVariableForm(): bool {
       return count($this->actions) > 0;
    }

    /**
     * @param array<string, DummyVariable> $variables
     * @return array<string, DummyVariable>
     */
    public function flattenVariables(array $variables, int $depth = 0): array {
        $flat = [];
        foreach ($variables as $baseName => $variable) {
            $flat[$baseName] = $variable;
            foreach ($variable->getObjectValuesDummy() as $propName => $value) {
                if ($variable->getValueType() === $value->getValueType()) continue;

                if (!$value->isObjectVariableType() or $depth >= 2) {
                    $flat[$baseName.".".$propName] = $value;
                    continue;
                }

                foreach ($this->flattenVariables([$baseName.".".$propName => $value], $depth + 1) as $name => $flattenVariable) {
                    $flat[$name] = $flattenVariable;
                }
            }
        }
        return $flat;
    }

    public function sendAddVariableForm(Player $player, CustomForm $origin, int $index): void {
        (new ListForm("@form.element.variableDropdown.createVariable"))
            ->addButtonsEach($this->actions, function (string $id) use ($player, $origin, $index) {
                $action = FlowItemFactory::get($id);

                return new Button($action->getName(), function () use ($player, $origin, $index, $action) {
                    $parents = Session::getSession($player)->get("parents");
                    /** @var FlowItemContainer $container */
                    $container = end($parents);
                    /** @var Recipe $recipe */
                    $recipe = array_shift($parents);
                    $variables = $recipe->getAddingVariablesBefore($action, $parents, FlowItemContainer::ACTION);

                    $form = $action->getEditForm($variables);
                    $form->addArgs($form, $action, function ($result) use ($player, $origin, $index, $action, $parents, $recipe, $container) {
                        if (!$result) {
                            $origin->resend([], ["@form.cancelled"]);
                            return;
                        }

                        if ($container instanceof Recipe) {
                            $place = array_search(Session::getSession($player)->get("action_list_clicked"), $container->getActions(), true);
                            if ($place !== false) {
                                $container->pushItem($place, $action, FlowItemContainer::ACTION);
                            } else {
                                $container->addItem($action, FlowItemContainer::ACTION);
                            }
                        } else {
                            $container1 = $parents[count($parents) - 2] ?? $recipe;
                            $place = array_search($container, $container1->getActions(), true);
                            $container1->pushItem($place, $action, FlowItemContainer::ACTION);
                        }
                        $add = $action->getAddingVariables();
                        $variables = array_merge($recipe->getAddingVariablesBefore($action, $parents, FlowItemContainer::ACTION), $add);

                        $indexes = [];
                        foreach ($origin->getContents() as $i => $content) {
                            if ($content instanceof VariableDropdown) {
                                $tmp = $content->getDefaultText();
                                $content->updateOptions($variables);
                                $content->updateDefault($index === $i ? array_key_first($add) : $tmp);
                                $indexes[$i] = $content->getDefault();
                            }
                        }

                        $origin->resend([], ["@form.added"], $indexes);
                    })->onReceive([new FlowItemForm(), "onUpdateAction"])->show($player);
                });
            })->addButton(new Button("@form.cancelAndBack", fn() => $origin->resend()))
            ->show($player);
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $selectedIndex = $response->getDropdownResponse();

        if ($this->isOptional() and $selectedIndex === 0) {
            $response->overrideResponse("");
            return;
        }

        if ($selectedIndex === $this->inputManuallyOptionIndex) {
            $response->setResend(true);
            $response->overrideElement(new ExampleInput($this->getText(), $this->getVariableType(), $this->getDefaultText(), !$this->isOptional()), $this->getDefaultText());
            return;
        }

        if ($selectedIndex === $this->createVariableOptionIndex) {
            $index = $response->getCurrentIndex();
            $response->setInterruptCallback(function () use($response, $player, $index) {
                $this->sendAddVariableForm($player, $response->getCustomForm(), $index);
                return true;
            });
            return;
        }

        $response->overrideResponse($this->variableNames[$selectedIndex]);
    }
}