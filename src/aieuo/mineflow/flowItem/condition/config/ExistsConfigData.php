<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExistsConfigData extends SimpleCondition {

    private ConfigArgument $config;
    private StringArgument $key;

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::EXISTS_CONFIG_DATA, FlowItemCategory::CONFIG);

        $this->setArguments([
            $this->config = new ConfigArgument("config", $config),
            $this->key = new StringArgument("key", $key, example: "aieuo"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->config->getConfig($source);
        $key = $this->key->getString($source);

        yield Await::ALL;
        return $config->getNested($key) !== null;
    }
}
