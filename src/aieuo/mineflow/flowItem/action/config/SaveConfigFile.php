<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use SOFe\AwaitGenerator\Await;

class SaveConfigFile extends SimpleAction {

    public function __construct(string $config = "") {
        parent::__construct(self::SAVE_CONFIG_FILE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->setArguments([
            new ConfigArgument("config", $config),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArguments()[0];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->getConfig()->getConfig($source);
        $config->save();

        yield Await::ALL;
    }
}
