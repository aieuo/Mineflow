<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\editor\FlowItemEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\controller\FlowItemFormController;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_key_first;
use function array_merge;
use function array_search;
use function count;
use function in_array;

abstract class VariableDropdown extends Dropdown {

    private string $defaultText;

    /** @var string[] */
    protected array $actions = [];
    private array $variableClasses;
    private array $variableNames = [];

    private bool $optional;

    private int $createVariableOptionIndex = -1;
    private int $inputManuallyOptionIndex = -1;

    /**
     * @param string $text
     * @param array<string, DummyVariable> $variables
     * @param string[] $variableClasses
     * @param string $default
     * @param bool $optional
     */
    public function __construct(string $text, array $variables = [], array $variableClasses = [], string $default = "", bool $optional = false) {
        $this->defaultText = $default;
        $this->variableClasses = $variableClasses;
        $this->optional = $optional;
        $options = $this->updateOptions($this->flattenVariables($variables));

        parent::__construct($text, $options, $this->findDefaultKey($default));
    }

    /**
     * @param array<string, DummyVariable> $variables
     * @return array
     */
    public function updateOptions(array $variables): array {
        $variableTypes = $this->variableClasses;
        $default = $this->defaultText;

        $options = [];
        foreach ($variables as $name => $variable) {
            if (!in_array($variable->getValueClass(), $variableTypes, true)) continue;

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
        $this->setDefaultIndex($this->findDefaultKey($default));
    }

    public function getVariableClasses(): array {
        return $this->variableClasses;
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
                // HACK: Prevent useless variables from being listed.
                if ($propName === "world" and $depth >= 1) continue;

                if (!$value->isObjectVariableType() or $depth >= 3 or in_array($propName, ["up", "down", "south", "north", "west", "east"], true)) {
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
        $actions = [];
        foreach ($this->actions as $id) {
            $action = FlowItemFactory::get($id);
            if ($action !== null) {
                $actions[] = $action;
            }
        }

        (new ListForm("@form.element.variableDropdown.createVariable"))
            ->addButtonsEach($actions, function (FlowItem $action) use ($player, $origin, $index) {
                return new Button($action->getName(), function () use ($player, $origin, $index, $action) {
                    Await::f2c(function () use($player, $origin, $index, $action) {
                        $recipe = FlowItemFormController::getEditingRecipe($player);
                        if ($recipe === null) return;

                        $variables = $recipe->getAddingVariablesUntil($action);

                        $container = FlowItemFormController::getEditingContainer($player) ?? $recipe;
                        $parentContainer = FlowItemFormController::getParentContainerOf($player, $container);
                        $editingItem = FlowItemFormController::getEditingItem($player);

                        $editor = $action->getNewItemEditor();
                        $editor->onStartEdit($player);
                        $result = yield from $editor->edit($player, $variables, true);
                        $editor->onFinishEdit($player);
                        if ($result === FlowItemEditor::EDIT_CLOSE) return;

                        if ($result === FlowItemEditor::EDIT_CANCELED) {
                            $origin->resend([], ["@form.cancelled"]);
                            return;
                        }

                        if ($container instanceof Recipe) {
                            $index = array_search(Session::getSession($player)->get("action_list_clicked"), $container->getItems(), true);
                            if ($index !== false) {
                                $container->pushAction($index, $action);
                            } else {
                                $container->addAction($action);
                            }
                        } elseif ($parentContainer !== null and $editingItem !== null) {
                            $index = array_search($editingItem, $parentContainer->getItems(), true);
                            if ($index !== false) {
                                $parentContainer->pushItem($index, $action);
                            } else {
                                $parentContainer->addItem($action);
                            }
                        }
                        $add = $action->getAddingVariables();
                        $variables = array_merge($recipe->getAddingVariablesUntil($action), $add);

                        $indexes = [];
                        foreach ($origin->getContents() as $i => $content) {
                            if ($content instanceof VariableDropdown) {
                                $tmp = $content->getDefaultText();
                                $content->updateOptions($variables);
                                $content->updateDefault($index === $i ? array_key_first($add) : $tmp);
                                $indexes[$i] = $content->getDefaultIndex();
                            }
                        }

                        $origin->resend([], ["@form.added"], $indexes);
                    });
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
            /** @var class-string<Variable>|Variable $class */
            $types = array_map(fn($class) => $class::getTypeName(), $this->variableClasses);
            $response->overrideElement(new ExampleInput($this->getText(), implode(", ", $types), $this->getDefaultText(), !$this->isOptional()), $this->getDefaultText());
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