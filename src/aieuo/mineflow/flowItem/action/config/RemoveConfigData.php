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

    private ConfigArgument $config;
    private StringArgument $key;

    public function __construct(string $config = "", string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            $this->config = new ConfigArgument("config", $config),
            $this->key = new StringArgument("key", $key, "@action.setConfig.form.key", example: "aieuo"),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->key->getString($source);

        $config = $this->config->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }
}
