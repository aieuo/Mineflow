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
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use SOFe\AwaitGenerator\Await;

class SetConfigData extends SimpleAction {

    public function __construct(string $config = "", string $key = "", string $value = "") {
        parent::__construct(self::SET_CONFIG_VALUE, FlowItemCategory::CONFIG, [FlowItemPermission::CONFIG]);

        $this->setArguments([
            ConfigArgument::create("config", $config),
            StringArgument::create("key", $key)->example("aieuo"),
            StringArgument::create("value", $value)->example("100"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArgument("config");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getValue(): StringArgument {
        return $this->getArgument("value");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);
        $value = $this->getValue()->getRawString();

        $helper = Mineflow::getVariableHelper();
        if ($helper->isSimpleVariableString($value)) {
            $variable = $source->getVariable(substr($value, 1, -1)) ?? VariableRegistry::global()->get(substr($value, 1, -1)) ?? $value;
            if ($variable instanceof IteratorVariable) {
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