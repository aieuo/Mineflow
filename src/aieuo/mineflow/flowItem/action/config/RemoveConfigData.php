<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use SOFe\AwaitGenerator\Await;

class RemoveConfigData extends SimpleAction {

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            new ConfigArgument("config", $config),
            new StringArgument("key", $key, "@action.setConfig.form.key", example: "aieuo"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArguments()[0];
    }

    public function getKey(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);

        $config = $this->getConfig()->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }
}
