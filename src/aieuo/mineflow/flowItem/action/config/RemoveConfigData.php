<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class RemoveConfigData extends SimpleAction {

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG, [FlowItemPermission::CONFIG]);

        $this->setArguments([
            ConfigArgument::create("config", $config),
            StringArgument::create("key", $key, "@action.setConfig.form.key")->example("aieuo"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArgument("config");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);

        $config = $this->getConfig()->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }
}