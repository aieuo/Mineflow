<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class ExistsConfigData extends SimpleCondition {

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::EXISTS_CONFIG_DATA, FlowItemCategory::CONFIG);

        $this->setArguments([
            ConfigArgument::create("config", $config),
            StringArgument::create("key", $key)->example("aieuo"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArgument("config");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->getConfig()->getConfig($source);
        $key = $this->getKey()->getString($source);

        yield Await::ALL;
        return $config->getNested($key) !== null;
    }
}