<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SaveConfigFile extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $config = "") {
        parent::__construct(self::SAVE_CONFIG_FILE, FlowItemCategory::CONFIG);

        $this->setConfigVariableName($config);
    }

    public function getDetailDefaultReplaces(): array {
        return ["config"];
    }

    public function getDetailReplaces(): array {
        return [$this->getConfigVariableName()];
    }

    public function getPermissions(): array {
        return [self::PERMISSION_CONFIG];
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->getConfig($source);
        $config->save();

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName()];
    }
}
