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
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class RemoveConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $config = "", private string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG);

        $this->setConfigVariableName($config);
    }

    public function getDetailDefaultReplaces(): array {
        return ["config", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->getConfigVariableName(), $this->getKey()];
    }

    public function getPermissions(): array {
        return [self::PERMISSION_CONFIG];
    }

    public function setKey(string $health): void {
        $this->key = $health;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "" and $this->key !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $source->replaceVariables($this->getKey());

        $config = $this->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@action.setConfig.form.key", "aieuo", $this->getKey(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey()];
    }
}
