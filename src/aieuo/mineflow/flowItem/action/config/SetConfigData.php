<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class SetConfigData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ConfigArgument $config;
    private StringArgument $key;
    private StringArgument $value;

    public function __construct(string $config = "", string $key = "", string $value = "") {
        parent::__construct(self::SET_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->config = new ConfigArgument("config", $config);
        $this->key = new StringArgument("key", $key, example: "aieuo");
        $this->value = new StringArgument("value", $value, example: "100");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->config->getName(), "key", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->config->get(), $this->key->get(), $this->value->get()];
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getValue(): StringArgument {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->config->isValid() and $this->key->isValid() and $this->value->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->key->getString($source);
        $value = $this->value->get();

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

        $config = $this->config->getConfig($source);
        $config->setNested($key, $value);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->config->createFormElement($variables),
            $this->key->createFormElement($variables),
            $this->value->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->config->set($content[0]);
        $this->key->set($content[1]);
        $this->value->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->config->get(), $this->key->get(), $this->value->get()];
    }
}
