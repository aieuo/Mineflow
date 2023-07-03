<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class SetConfigData extends SimpleAction {

    public function __construct(string $config = "", string $key = "", string $value = "") {
        parent::__construct(self::SET_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            new ConfigArgument("config", $config),
            new StringArgument("key", $key, example: "aieuo"),
            new StringArgument("value", $value, example: "100"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArguments()[0];
    }

    public function getKey(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getValue(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);
        $value = $this->getValue()->get();

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

        $config = $this->getConfig()->getConfig($source);
        $config->setNested($key, $value);

        yield Await::ALL;
    }
}
