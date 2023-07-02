<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class SetConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $config = "", private string $key = "", private string $value = "") {
        parent::__construct(self::SET_CONFIG_VALUE, FlowItemCategory::CONFIG, [FlowItemPermission::CONFIG]);

        $this->setConfigVariableName($config);
    }

    public function getDetailDefaultReplaces(): array {
        return ["config", "key", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getConfigVariableName(), $this->getKey(), $this->getValue()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
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

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@action.setConfig.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.setConfig.form.value", "100", $this->getValue(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setValue($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey(), $this->getValue()];
    }
}
