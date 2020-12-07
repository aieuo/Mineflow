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
use pocketmine\Player;

abstract class VariableDropdown extends Dropdown {

    /** @var string */
    protected $variableType;

    /** @var string */
    private $defaultText;

    /** @var string[] */
    protected $actions = [];
    /* @var array */
    private $variableTypes;

    public const VALUE_SEPARATOR_LEFT = " §7(";
    /* @var bool */
    private $optional;

    public function __construct(string $text, array $variables = [], array $variableTypes = [], string $default = "", bool $optional = false) {
        $this->defaultText = $default;
        $this->variableTypes = $variableTypes;
        $this->optional = $optional;
        $options = $this->updateOptions($this->flattenVariables($variables));

        $defaultKey = $this->findDefaultKey($default);
        parent::__construct($text, $options, $defaultKey >= 0 ? $defaultKey : 0);
    }

    public function updateOptions(array $variables): array {
        $variableTypes = $this->variableTypes;
        $default = $this->defaultText;

        $variables = array_filter($variables, function (DummyVariable $v) use ($variableTypes) {
            return in_array($v->getValueType(), $variableTypes, true);
        });
        $options = array_values(array_unique(array_map(function (DummyVariable $v) {
            return empty($v->getDescription()) ? $v->getName() : ($v->getName().self::VALUE_SEPARATOR_LEFT.$v->getDescription().")");
        }, $variables)));

        if ($this->findDefaultKey($default, $options) === -1) {
            $options[] = $default;
        }

        if ($this->isOptional()) $options[] = Language::get("form.element.variableDropdown.none");
        $options[] = Language::get("form.element.variableDropdown.createVariable");
        $options[] = Language::get("form.element.variableDropdown.inputManually");
        $this->options = $options;
        return $options;
    }

    public function findDefaultKey(string $default, array $options = null): int {
        if ($default === "" and !$this->isOptional()) return 0;

        $options = $options ?? $this->options;
        if ($default === "") return count($options) - 3;

        foreach ($options as $i => $option) {
            if (strpos(explode(self::VALUE_SEPARATOR_LEFT, $option)[0], $default) !== false) return $i;
        }
        return -1;
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

    /**
     * @param DummyVariable[] $variables
     * @return DummyVariable[]
     */
    public function flattenVariables(array $variables): array {
        $flat = [];
        foreach ($variables as $variable) {
            $flat[] = $variable;
            foreach ($variable->getObjectValuesDummy() as $value) {
                if (!$value->isObjectVariableType()) {
                    $flat[] = $value;
                    continue;
                }

                foreach ($this->flattenVariables([$value]) as $flattenVariable) {
                    $flat[] = $flattenVariable;
                }
            }
        }
        return $flat;
    }

    public function sendAddVariableForm(Player $player, CustomForm $origin, int $index): void {
        (new ListForm("@form.element.variableDropdown.createVariable"))
            ->addButtonsEach($this->actions, function (ListForm $form, string $id) use ($player, $origin, $index) {
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

                        /** @var VariableDropdown $dropdown */
                        $dropdown = $origin->getContent($index);
                        $dropdown->updateOptions($variables);
                        $dropdown->updateDefault($add[0]->getName());

                        $origin->resend([], ["@form.added"], [$index => $dropdown->getDefault()]);
                    })->onReceive([new FlowItemForm(), "onUpdateAction"])->show($player);
                });
            })->addButton(new Button("@form.cancelAndBack", function () use($origin) {
                $origin->resend();
            }))->show($player);
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $options = $this->getOptions();
        $maxIndex = count($options) - 1;
        $data = $response->getDropdownResponse();

        if ($data === $maxIndex) { // 手動で入力する時
            $response->setResend(true);
            $response->overrideElement(new ExampleInput($this->getText(), $this->getVariableType(), $this->getDefaultText(), !$this->isOptional()), $this->getDefaultText());
        } elseif ($data === $maxIndex - 1) { // 変数を追加するとき
            $index = $response->getCurrentIndex();
            $response->setInterruptCallback(function () use($response, $player, $index) {
                $this->sendAddVariableForm($player, $response->getCustomForm(), $index);
                return true;
            });
        } elseif ($this->isOptional() and $data === $maxIndex - 2) {
            $response->overrideResponse("");
        } else { // 変数名を選択したとき
            $response->overrideResponse(explode(VariableDropdown::VALUE_SEPARATOR_LEFT, $options[$data])[0]); // TODO: 文字列操作せずに変数名だけ取り出す
        }
    }
}