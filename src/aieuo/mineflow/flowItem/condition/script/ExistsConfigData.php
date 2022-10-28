<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class ExistsConfigData extends FlowItem implements Condition, ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;
    use ConditionNameWithMineflowLanguage;

    public function __construct(string $config = "", private string $key = "") {
        parent::__construct(self::EXISTS_CONFIG_DATA, FlowItemCategory::CONFIG);

        $this->setConfigVariableName($config);
    }

    public function getDetailDefaultReplaces(): array {
        return ["config", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->getConfigVariableName(), $this->getKey()];
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "" and $this->getKey() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->getConfig($source);

        $key = $source->replaceVariables($this->getKey());

        yield Await::ALL;
        return $config->getNested($key) !== null;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@condition.existsConfig.form.key", "aieuo", $this->getKey(), true),
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
