<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\config;

use aieuo\mineflow\flowItem\argument\FileNameArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class ExistsConfigFile extends SimpleCondition {

    public function __construct(string $fileName = "") {
        parent::__construct(self::EXISTS_CONFIG_FILE, FlowItemCategory::CONFIG);

        $this->setArguments([
            FileNameArgument::create("name", $fileName),
        ]);
    }

    public function getFileName(): StringArgument {
        return $this->getArgument("name");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = Utils::getValidFileName($this->getFileName()->getString($source));

        yield Await::ALL;
        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }
}