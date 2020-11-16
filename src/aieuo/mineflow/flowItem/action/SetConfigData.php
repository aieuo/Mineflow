<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;

class SetConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::SET_CONFIG_VALUE;

    protected $name = "action.setConfigData.name";
    protected $detail = "action.setConfigData.detail";
    protected $detailDefaultReplace = ["config", "key", "value"];

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_2;

    /** @var string */
    private $key;
    /* @var string */
    private $value;

    public function __construct(string $config = "", string $key = "", string $value = "") {
        $this->setConfigVariableName($config);
        $this->key = $key;
        $this->value = $value;
    }

    public function setKey(string $health): void {
        $this->key = $health;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "" and $this->key !== "" and $this->value !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName(), $this->getKey(), $this->getValue()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $key = $origin->replaceVariables($this->getKey());

        $value = $this->getValue();

        $helper = Main::getVariableHelper();
        if ($helper->isVariableString($value)) {
            $variable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1)) ?? $value;
            if ($variable instanceof ListVariable) {
                $value = $variable->toArray();
            } else if ($variable instanceof NumberVariable) {
                $value = $variable->getValue();
            } else {
                $value = (string)$variable;
            }
        } else {
            $value = $helper->replaceVariables($value, $origin->getVariables());
            if (is_numeric($value)) $value = (float)$value;
        }

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $config->setNested($key, $value);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
                new ExampleInput("@action.setConfigData.form.key", "aieuo", $this->getKey(), true),
                new ExampleInput("@action.setConfigData.form.value", "100", $this->getValue(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setValue($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey(), $this->getValue()];
    }
}