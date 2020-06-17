<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;

class SetConfigData extends Action implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::SET_CONFIG_DATA;

    protected $name = "action.setConfigData.name";
    protected $detail = "action.setConfigData.detail";
    protected $detailDefaultReplace = ["config", "key", "value"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_2;

    /** @var string */
    private $key;
    /* @var string */
    private $value;

    public function __construct(string $name = "config", string $key = "", string $value = "") {
        $this->configVariableName = $name;
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $key = $origin->replaceVariables($this->getKey());

        $value = $this->getValue();

        $helper = Main::getVariableHelper();
        if (!$helper->isVariableString($value)) {
            $value = $helper->replaceVariables($value, $origin->getVariables());
            if (is_numeric($value)) $value = (float)$value;
        } else {
            $variable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1)) ?? $value;
            if ($variable instanceof ListVariable) {
                $value = $variable->toArray();
            } else if ($variable instanceof NumberVariable) {
                $value = $variable->getValue();
            } else {
                $value = (string)$variable;
            }
        }

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $config->setNested($key, $value);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getConfigVariableName()),
                new Input("@action.setConfigData.form.key", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getKey()),
                new Input("@action.setConfigData.form.value", Language::get("form.example", ["100"]), $default[3] ?? $this->getValue()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $errors[] = ["@form.insufficient", 3];
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setValue($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey(), $this->getValue()];
    }
}