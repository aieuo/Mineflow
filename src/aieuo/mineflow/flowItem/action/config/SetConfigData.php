<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;

class SetConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected string $id = self::SET_CONFIG_VALUE;

    protected string $name = "action.setConfigData.name";
    protected string $detail = "action.setConfigData.detail";
    protected array $detailDefaultReplace = ["config", "key", "value"];

    protected string $category = FlowItemCategory::CONFIG;

    protected int $permission = self::PERMISSION_LEVEL_2;

    private string $key;
    private string $value;

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $key = $source->replaceVariables($this->getKey());
        $value = $this->getValue();

        $helper = Mineflow::getVariableHelper();
        if ($helper->isSimpleVariableString($value)) {
            $variable = $source->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1)) ?? $value;
            if ($variable instanceof ListVariable) {
                $value = $variable->toArray();
            } else if ($variable instanceof NumberVariable) {
                $value = $variable->getValue();
            } else {
                $value = $source->replaceVariables((string)$variable);
            }
        } else {
            $value = $helper->replaceVariables($value, $source->getVariables());
            if (is_numeric($value)) $value = (float)$value;
        }

        $config = $this->getConfig($source);

        $config->setNested($key, $value);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@action.setConfigData.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.setConfigData.form.value", "100", $this->getValue(), true),
        ];
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
