<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class SaveConfigFile extends SimpleAction {

    public function __construct(string $config = "") {
        parent::__construct(self::SAVE_CONFIG_FILE, FlowItemCategory::CONFIG, [FlowItemPermission::CONFIG]);

        $this->setArguments([
            ConfigArgument::create("config", $config),
        ]);
    }

    public function getConfig(): ConfigArgument {
        return $this->getArgument("config");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->getConfig()->getConfig($source);
        $config->save();

        yield Await::ALL;
    }
}